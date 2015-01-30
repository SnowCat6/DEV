<?
//	+function module_logAdminTools
function module_logAdminTools($cal, &$menu)
{
	if (!access('write', 'undo')) return;
	$menu['Undo/Redo#ajax']	= getURL('admin_logAdmin');
}

function module_logAdmin($val, $data)
{
	if (!access('write', 'undo')) return;
	
	$db	= new dbRow('log_tbl', 'log_id');
	
	if ($id = getValue('undo')){
		messageBox(module('logUndo', $id));
	}
	
	$sql	= array();
	$filter	= array();
	$search	= getValue('search');
	if ($val = (int)$search['userIP']){
		$sql[]	= "userIP = $val";
		$ip		= GetStringIP($val);
		$filter["IP адрес $ip"]	= getURL('#');
	}
	if ($val = (int)$search['userID']){
		$sql[]	= "user_id = $val";
		$filter["Номер пользователя $val"]	= getURL('#');
	}
	if ($val = $search['source']){
		$v		= dbEncString($db, $val);
		$sql[]	= "source = $v";
		$filter["Источник $val"]	= getURL('#');
	}
	
	if (testValue('clear')){
		$table	= $db->table();
		$db->exec("DELETE FROM $table");
		messageBox('Лог действий удален');
		logData('Лог действий удален');
	}

	$db->order	= 'log_id DESC';
	$db->open($sql);
	$p	= dbSeek($db, 50, array('search' => $search));
?>
<link rel="stylesheet" type="text/css" href="../../../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/undoAdmin.css">
{{page:title=Лог пользовательских действий}}
<a href="{{url:#=clear}}">Очистить историю</a>
<? if ($filter){ ?>
<p>
Фильтр:
<? foreach($filter as $name => $url){ ?>
<a href="{$url}">{$name}</a>
<? } ?>
</p>
<? } ?>

{!$p}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table adminUndo">
  <tbody>
    <tr>
      <th nowrap="nowrap">Дата</th>
      <th nowrap="nowrap">User/IP</th>
      <th nowrap="nowrap">Источник</th>
      <th width="100%">Сообщение</th>
      <th>&nbsp;</th>
    </tr>
<? while($data = $db->next()){
	$id		= $db->id();
	$ip		= GetStringIP($data['userIP']);
	$userID	= $data['user_id'];
	
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
?>
<tr class="undo_{$action}">
    <td nowrap="nowrap" title="{{date:%d.%m.%Y %H:%i:%s=$data[date]}}">{{date:%d.%m.%Y=$data[date]}}</td>
    <td nowrap="nowrap">
      	<a href="{{url:#=search.userID:$userID}}">[{$userID}]</a>
        <a href="{{url:#=search.userIP:$data[userIP]}}">{$ip}</a>
    </td>
    <td nowrap="nowrap">
        <a href="{{url:#=search.source:$data[source]}}">{$data[source]}</a>
    </td>
      <td>{$data[message]}</td>
      <td>
	  <? if ($undo && $undo['action']){ $info = implode("\r\n", $undo['info']); ?>
      <a href="{{url:#=undo:$id}}" title="{$info}">{$data[action]}</a>
      <? } ?>
      </td>
    </tr>
<? } ?>
  </tbody>
</table>

<? } ?>
