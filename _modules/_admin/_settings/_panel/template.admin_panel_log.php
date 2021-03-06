<?
function admin_panel_log($data)
{
	if (!hasAccessRole('developer')) return;
	
	$eceuteTime	= round(getmicrotime() - sessionTimeStart, 3);
	$memUse		= function_exists('memory_get_peak_usage')?memory_get_peak_usage():'';
	$memUse		= round($memUse / (1024*1024), 2);
	if ($memUse) $memUse = "Выделеная память: <b>$memUse</b> Mb.";
	
	$names	= array();
	$names['error']	= 'Ошибки';
	$names['trace']	= 'Трассировка';
	$names['cache']	= 'Кеш';
	$names['sql']	= 'SQL';
	

	$log	= config::get('log', array());
	foreach($log as $name => $logTrace)
	{
		if ($name == 'error') continue;
		foreach($logTrace as &$logValue)
		{
			$traceName	= $logValue[0];
			$traceValue	= $logValue[1];
			if (is_int(strpos($traceName, 'error'))){
				$log['error'][]	= array("$name:$traceName", $traceValue);
			}
		}
	}
	$log2	= array();
	foreach($names as $name => $logName)
	{
		$val	= $log[$name];
		if (!$val) continue;
		unset($log[$name]);
		$log2[$logName]	= $val;
	}
	foreach($log as $name => $val)
	{
		foreach($val as &$logValue)
		{
			$traceName	= $logValue[0];
			$traceValue	= $logValue[1];
			$log2['Другое'][]	= array("$name:$traceName", $traceValue);
		}
	}
?>
Время выполнения: <b>{$eceuteTime}</b> сек. {!$memUse}
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<? foreach($log2 as $name => &$logTrace){?>
    <li class="ui-corner-top"><a href="#log{$name}">{$name}</a></li>
<? } ?>
</ul>


<? foreach($log2 as $name => &$logTrace){ ?>
<div id="log{$name}" class="admin-selectable"  unselectable="off">
<pre>
<? foreach($logTrace as &$logValue)
{
	$traceName	= $logValue[0];
	$traceValue	= $logValue[1];
	
	$traceName	= htmlspecialchars($traceName);
	if ($traceName) $traceName	.= ' &nbsp; ';

	$class	=	'';
	if (is_int(strpos($traceName, 'error'))) $class	= ' class="errorMessage"';
?>
<span {!$class}>{!$traceName} {$traceValue}</span>
<? } ?>
</pre>
</div>
<? } ?>

</div>
{{script:adminTabs}}
<? return 'Лог исполнения'; } ?>