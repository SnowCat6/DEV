<? function cron_all($val, $data)
{
	if (!access('write', 'cron:')) return;	
	
	m('page:title', 'Задания');
	
	$crons = getCacheValue('cronWork');
	if (!is_array($crons)) $crons = array();
	$ini	= readCronIni();
	if (is_array($cronTaskDisable = getValue('cronTaskDisable'))){
		foreach($cronTaskDisable as $name => $disable){
			$ini[$name]['disable'] = $disable;
		}
		writeCronIni($ini);
	}
	m('script:ajaxLink');
	if ($name = getValue('log')){
		$n = htmlspecialchars($name);
		m('page:title', "Лог: $name");
		echo urldecode($ini[$name]['log']);
		return;
	}
?>
<form action="{{url:#}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>Запретить выполнение</th>
    <th nowrap="nowrap">Последние выполнение</th>
    <th width="100%">Задание</th>
  </tr>
<?
foreach($crons as $name => $command)
{
	$bDisable	=	$ini[$name]['disable'];

	$lastRun	=	$ini[$name]['lastRun'];
	if ($lastRun) $lastRun = date('d.m.Y H:i:s', $lastRun);
	else $lastRun = '-';

	$lastRunEnd	=	$ini[$name]['lastRunEnd'];
	if ($lastRunEnd){
		if ((int)$lastRunEnd){
			$lastRunEnd = date('d.m.Y H:i:s', $lastRunEnd);
			$timeout	= $ini[$name]['lastRunEnd'] - $ini[$name]['lastRun'];
		}else{
			$timeout	= time() - $ini[$name]['lastRun'];
		}
		if ($timeout == 0) $timeout = '';
		else
		if ($timeout < 60) $timeout = round($timeout) . ' сек.';
		else $timeout = round($timeout / 60) . ' мин.';
		$lastRunEnd .= ' '  . $timeout;
	}else $lastRunEnd = '-';
	
	$class	= $bDisable?' checked="checked"':'';
?>
  <tr>
    <td>
    <input name="cronTaskDisable[{$name}]" type="hidden" value="" {!$class} />
    <input name="cronTaskDisable[{$name}]" type="checkbox" value="1" {!$class} />
    </td>
    <td nowrap="nowrap">
    <div>{$lastRun}</div>
    <div>{$lastRunEnd}</div>
    </td>
    <td><a href="{{url:#=log:$name}}" id="ajax">{$name}</a></td>
  </tr>
<? } ?>
</table>
<p><input type="submit" class="button" value="Сохранить" /></p>
</form>
<h2>Коммандная строка для запуска Cron:</h2>
<div><?= execPHPshell('"'.globalRootPath.'/index.php'.'"')?></div>
<? } ?>
