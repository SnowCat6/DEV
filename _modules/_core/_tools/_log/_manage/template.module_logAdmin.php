<?
//	+function module_logAdminTools
function module_logAdminTools($cal, &$menu)
{
	if (!hasAccessRole('admin,developer')) return;
	$menu['Лог действий#ajax']	= getURL('admin_logAdmin');
}
function module_logAdmin($val, $data)
{
	if (!hasAccessRole('admin,developer')) return;
	
	$db	= new dbRow('log_tbl', 'log_id');
	
	if ($val = getValue('undo'))
	{
		$data	=$db->openID($val);
		$undo	= $data['data']['undo'];
		if ($undo){
			if (module($undo['action'], $undo['data'])){
				messageBox('Отмена действия');
			}else{
				messageBox("Неудачная отмена действия '$undo'");
			}
		}
	}else
	if ($val = getValue('redo'))
	{
		$data	=$db->openID($val);
		$redo	= $data['data']['redo'];
		if ($redo){
			if (module($redo['action'], $redo['data'])){
				messageBox('Отмена действия');
			}else{
				messageBox("Неудачная отмена действия '$redo'");
			}
		}
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

	$db->order	= 'date DESC';
	$db->open($sql);
	$p	= dbSeek($db, 50, array('search' => $search));
?>
<link rel="stylesheet" type="text/css" href="../../../../../_templates/baseStyle.css">
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
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tbody>
    <tr>
      <th nowrap="nowrap">Дата</th>
      <th nowrap="nowrap">User/IP</th>
      <th nowrap="nowrap">Источник</th>
      <th width="100%">Сообщение</th>
      <th width="100%">&nbsp;</th>
    </tr>
<? while($data = $db->next()){
	$id		= $db->id();
	$ip		= GetStringIP($data['userIP']);
	$userID	= $data['user_id'];
	$undo	= $data['data']['undo'];
	$redo	= $data['data']['redo'];
?>
    <tr>
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
	  <? if ($undo){ ?>
      <a href="{{url:#=undo:$id}}">undo</a>
      <? } ?>
	  <? if ($redo){ ?>
      <a href="{{url:#=redo:$id}}">redo</a>
      <? } ?>
      </td>
    </tr>
<? } ?>
  </tbody>
</table>

<? } ?>
