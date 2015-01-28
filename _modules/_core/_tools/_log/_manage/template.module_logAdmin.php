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
Фильртр:
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
    </tr>
<? while($data = $db->next()){
	$ip		= GetStringIP($data['userIP']);
	$userID	= $data['user_id'];
?>
    <tr>
      <td nowrap="nowrap" title="{{date:%d.%m.%Y %H:%i:%s=$data[date]}}">{{date:%d.%m.%Y=$data[date]}}</td>
      <td nowrap="nowrap">
      	<a href="{{url:#=search.userID:$userID}}">[{$userID}]</a>
        <a href="{{url:#=search.userIP:$data[userIP]}}">{$ip}</a>
        </td>
      <td nowrap="nowrap">{$data[source]}</td>
      <td>{$data[message]}</td>
    </tr>
<? } ?>
  </tbody>
</table>

<? } ?>
