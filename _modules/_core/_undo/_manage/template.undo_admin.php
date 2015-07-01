<?
//	+function undo_tools
function undo_tools($db, $val, &$menu)
{
	if (!access('read', 'undo')) return;
	$menu['Undo/Redo#ajax']	= getURL('admin_undo');
}

//	+function undo_admin
function undo_admin($db, $val, $data)
{
	if (!access('read', 'undo')) return;
	
	if ($id = getValue('undo')){
		messageBox(module("undo:undo:$id"));
	}
	if ($id = getValue('undo_info')){
		return module("undo:undo_info:$id");
	}
	
	$sql	= array();
	$filter	= array();
	$search	= getValue('search');
	
	if (!access('write', 'undo')){
		$search['userID']	= userID();
	}
	
	if ($val = (int)$search['userIP']){
		$sql[]	= "userIP = $val";
		$ip		= GetStringIP($val);

		$filter['userIP']	= "IP адрес $ip";
	}
	if ($val = (int)$search['userID']){
		$sql[]	= "user_id = $val";

		$dbUser		= module('user:find', array('id' => $val));
		$userData	= $dbUser->next();
		$userName	= $userData?mEx("user:name", $userData):"[$val]";

		$filter['userID']	= $userName;
	}
	if ($val = $search['source']){
		$v		= dbEncString($db, $val);
		$sql[]	= "source = $v";
		$filter['source']	= "Источник $val";
	}
	
	if (testValue('clear') && access('delete', 'undo'))
	{
		$db->open("action IN ('undo', 'redo')");
		while($data = $db->next())
		{
			$undo	= $data['data'];
			$clean	= $undo['clean'];
			if ($clean) module($clean, $undo['data']);
		}
		
		$table	= $db->table();
		$db->exec("DELETE FROM $table");
		messageBox('Лог действий удален');
		logData('Лог действий удален');
	}

	$db->order	= 'log_id DESC';
	$db->open($sql);
	$p	= dbSeek($db, 50, array('search' => $search));
	$userID	= userID();
?>
<link rel="stylesheet" type="text/css" href="../../../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/undoAdmin.css">
{{page:title=Лог пользовательских действий}}
{{script:ajaxLink}}
<div class="seekLink">
<? if (access('delete', 'undo')){ ?>
<p>
	<a href="{{url:#=clear}}">Очистить историю</a>
</p>
<? } ?>
<p>
	<a href="{{url:#=search.userID:$userID}}">Только свои</a>
</p>
<? if ($filter){ ?>
<p>
Фильтр:
<? foreach($filter as $name => $title){
	$s 			= $search;
	$s[$name]	= '';
	removeEmpty($s);
?>
	<a href="{{url:#=search:$s}}">{$title}</a>
<? } ?>
</p>
<? } ?>

{!$p}
{{script:jq_ui}}
<script src="script/undoAdmin.js"></script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table adminUndo">
  <tbody>
    <tr>
      <th>&nbsp;</th>
      <th nowrap="nowrap">Дата</th>
      <th nowrap="nowrap">User/IP</th>
      <th nowrap="nowrap">Источник</th>
      <th width="100%">Сообщение</th>
      </tr>
<? while($data = $db->next()){
	$id		= $db->id();
	$ip		= GetStringIP($data['userIP']);
	$userID	= $data['user_id'];
	if ($userID){
		$dbUser		= module('user:find', array('id' => $userID));
		$userData	= $dbUser->next();
		$userName	= $userData?mEx("user:name", $userData):"[$userID]";
	}else{
		$userName	= '';
	}
	
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
?>
<tr class="undo_{$action}">
  <td><? if ($undo && $undo['action']){ ?>
    <a href="{{url:#=undo:$id}}" rel="{{url:#=undo_info:$id}}" class="-undo_action">{$data[action]}</a>
    <? } ?></td>
    <td nowrap="nowrap" title="{{date:%d.%m.%Y %H:%i:%s=$data[date]}}">{{date:%d.%m.%Y=$data[date]}}</td>
    <td nowrap="nowrap">
<?
$s = $search;
$s['userID']	= $userID;
?>
      	<a href="{{url:#=search:$s}}">{!$userName}</a>
<?
$s = $search;
$s['userIP']	= $data['userIP'];
?>
       <a href="{{url:#=search:$s}}">{$ip}</a>
    </td>
    <td nowrap="nowrap">
<?
$s = $search;
$s['source']	= $data['source'];
?>
        <a href="{{url:#=search:$s}}">{$data[source]}</a>
    </td>
      <td>{$data[message]}</td>
      </tr>
<? } ?>
  </tbody>
</table>
</div>
<? } ?>
