<? function import_ui($val, &$data)
{
	m('page:title', 'Импорт');
	
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
?>
<form action="<? module("url:import"); ?>" method="post">
<? importInfo() ?>
</form>
<? } ?><? function importInfo()
{
	$locks	= array();
	event('import.source', $locks);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="table">
  <tr>
    <th>Источник</th>
    <th width="100%">Статус</th>
    <th>Действия</th>
  </tr>
<? foreach($locks as $name => &$synch)
{
	$status	= $synch->getValue('status');
	if (!$status) $status = '---';
	
	$lockInfo	= '';
	$timeout	= $synch->lockTimeout();
	if ($timeout){
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
    <td nowrap="nowrap" title="<? if(isset($info["userInfo"])) echo htmlspecialchars($info["userInfo"]) ?>"><? if(isset($name)) echo htmlspecialchars($name) ?></td>
    <td>
    <? if(isset($status)) echo htmlspecialchars($status) ?>
    <div><? if(isset($lockInfo)) echo $lockInfo ?></div>
    </td>
    <td nowrap="nowrap"><? switch($status){ ?><? case '---': ?>
<div><input type="submit" class="button" name="doSynch[<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="Начать" />
<? break; ?><? case 'complete': ?>
<div><input type="submit" class="button" name="doSynchRepeat[<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="Повторить" />
<? break; ?><? default: ?>
<div><input type="submit" class="button" name="doCancel[<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="Отменить" />
<? break; ?><? } ?></td>
  </tr>
<? } ?>
</table>

<? } ?>