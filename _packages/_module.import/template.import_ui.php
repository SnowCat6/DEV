<? function import_ui($val, &$data)
{
	m('page:title', 'Импорт');
	if (testValue('ajax'))
		return importInfo(true);
	
	$delete	= getValue('doDelete');
	if (is_array($delete) && $delete){
		foreach($delete as $name => &$val) $val = $name;
		event('import.delete', $delete);
	}

	$cancel	= getValue('doCancel');
	if (is_array($cancel) && $cancel){
		foreach($cancel as $name => &$val) $val = $name;
		event('import.cancel', $cancel);
	}

	$cancel	= getValue('doSynchRepeat');
	if (is_array($cancel) && $cancel){
		foreach($cancel as $name => &$val) $val = $name;
		event('import.cancel',	$cancel);
		event('import.synch', 	$cancel);
	}

	$synch	= getValue('doSynch');
	if (is_array($synch) && $synch){
		foreach($synch as $name => &$val) $val = $name;
		event('import.synch', $synch);
	}
	m('script:jq');
?>
<form action="{{url:import}}" method="post" id="reload">
<div><? importInfo() ?></div>
<script>
function importTimeout(){
	$("#reload > div").load($("#reload").attr("action") + "?ajax=result", function(){
		$(document).trigger("jqReady");
		setTimeout(importTimeout, 5*1000);
	}, function(){
		setTimeout(importTimeout, 5*1000);
	});
}
$(importTimeout);
</script>
</form>
<? } ?>
<? function importInfo($bDoSynch = false)
{
	$locks	= array();
	event('import.source', $locks);
	if ($bDoSynch){
		foreach($locks as $name => &$s){
			$status		= $s->getValue('status');
			if ($status == 'complete') continue;
			if ($s->lockTimeout()) break;

			$s2	= array($name => $name);
			event('import.synch', $s2);
			break;
		}
	}
	m('script:ajaxLink');
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="table">
  <tr>
    <th>&nbsp;</th>
    <th>Источник</th>
    <th>Статус</th>
    <th>&nbsp;</th>
    <th>Процесс</th>
    <th width="100%">Статистика</th>
    <th width="100%">&nbsp;</th>
    <th>Действия</th>
  </tr>
<? foreach($locks as $name => &$synch)
{
	$status		= $synch->getValue('status');
	if (!$status) $status = '---';
	
	$progress	= '';
	$percent	= (float)$synch->getValue('percent');
	$progress	.= "$percent%";
	
	$lockInfo	= '';
	$timeout	= $synch->lockTimeout();
	if ($timeout)
	{
		$info		= $synch->info();
		$maxLock	= $synch->lockMaxTimeout();
		$userIP		= GetStringIP($info['userIP']);
		$userID		= $info['userID'];
		
		$dbUser		= module('user');
		$userData	= $dbUser->openID($userID);
		$userName	= m('user:name', $userData);

		$lockInfo .= "<div>Locked: $timeout/$maxLock сек.</div>";
		$lockInfo .= "<div>UserIP: $userIP, UserID: $userID $userName</div>";
	}
?>
  <tr>
    <td>
		<input type="submit" class="button" name="doDelete[{$name}]" value="x" />
    </td>
    <td nowrap="nowrap" title="{$info[userInfo]}">{$name}</td>
    <td nowrap="nowrap">{$status}</td>
    <td nowrap="nowrap">{!$progress}</td>
    <td nowrap="nowrap">{!$lockInfo}</td>
    <td nowrap="nowrap"><?
$statistic	= $synch->getValue('statistic');
if (!$statistic) $statistic = array();

	echo'<div>',
		'Каталоги: ',
		'pass ',	(int)$statistic['category']['pass'], ', ',
		'add: ',	(int)$statistic['category']['add'], ', ',
		'update: ',	(int)$statistic['category']['update'], ', ',
		'error: ',	(int)$statistic['category']['error'],
		'</div>';
	
	echo'<div>',
		'Товары: ',
		'pass ',	(int)$statistic['product']['pass'], ', ',
		'add: ',	(int)$statistic['product']['add'], ', ',
		'update: ',	(int)$statistic['product']['update'], ', ',
		'error: ',	(int)$statistic['product']['error'],
		'</div>';

    ?></td>
    <td nowrap="nowrap">
<? if ($synch->logRead(1)){ ?>
    <a href="{{url:import_log=name:$name}}" id="ajax">лог импорта</a>
<? } ?>
    </td>
    <td nowrap="nowrap"><? switch($status){ ?>
<? case '---': ?>
<div><input type="submit" class="button w100" name="doSynch[{$name}]" value="Начать" /></div>
<? break; ?>
<? case 'complete': ?>
<div><input type="submit" class="button w100" name="doSynchRepeat[{$name}]" value="Повторить" /></div>
<? break; ?>
<? default: ?>
<div>
<input type="submit" class="button" name="doSynch[{$name}]" value="Продолжить" />
<input type="submit" class="button" name="doCancel[{$name}]" value="||" />
</div>
<? break; ?>
<? } ?></td>
  </tr>
<? } ?>
</table>

<? } ?>