<?
function import_log($val, &$data)
{
	$locks	= array();
	event('import.source', $locks);

	$logFile	= getValue('name');
	$synch		= $locks[$logFile];
	if (!isset($synch)) return;

	m('page:title', "Лог: $logFile");
	
	$max		= 50;
	$nLineTo	= $synch->logLines();
	$nLineFrom	= max(0, $nLines - $max);
	$lines		= $synch->logRead($nLineFrom, $max);
?>
<table width="100%" cellpadding="0" cellspacing="0" class="table">
<? foreach($lines as $nLine => &$line){ ?>
<tr>
    <td>{$nLine}</td>
    <td nowrap="nowrap">{$line[date]}</td>
    <td width="100%">{!$line[message]}</td>
</tr>
<? } ?>
</table>
<? } ?>