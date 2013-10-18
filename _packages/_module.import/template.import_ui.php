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
<form action="{{url:import}}" method="post">
<? importInfo() ?>
</form>
<? } ?>
<? function importInfo()
{
	$locks	= array();
	event('import.source', $locks);
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="table">
  <tr>
    <th>&nbsp;</th>
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
    <td>
		<input type="submit" class="button" name="doDelete[{$name}]" value="x" />
    </td>
    <td nowrap="nowrap" title="{$info[userInfo]}">{$name}</td>
    <td>
    {$status}
    <div>{!$lockInfo}</div>
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