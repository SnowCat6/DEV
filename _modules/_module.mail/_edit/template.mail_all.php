<? function mail_all($db, $val, $data)
{
	if (!access('read', 'mail:')) return;
//	if (!hasAccessRole('admin,developer,writer,manager')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');
	
	if (is_array($ids = getValue('mailDelete'))){
		$db->delete($ids);
	}

	$db->order = 'dateSend DESC';
	$db->open();
	$p = dbSeek($db, 15);
?>
<link rel="stylesheet" type="text/css" href="../../_module.admin/admin.css">
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
{{page:title=Отправленные письма}}
{!$p}
<form action="{{getURL:admin_mail}}" method="post" class="admin ajaxFormNow ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>Дата</th>
    <th>Кому</th>
    <th>Заголовок</th>
</tr>
<?
while($data = $db->next())
{
	$id		= $db->id();
	$date	= makeDate($data['dateSend']);
	if (date('Yz', $date) == date('Yz')) $date = date('H:i', $date);
	else $date = date('d.m.Y', $date);
	
	$class = " class=\"mail_$data[mailStatus]\"";
?>
<tr {!$class}>
    <td nowrap>
    <input name="mailDelete[]" type="checkbox" value="{$id}" />
    <a href="{{getURL:admin_mail$id}}" id="ajax">{$date}</a>
    </td>
    <td>{$data[to]}</td>
    <td>{$data[subject]}</td>
</tr>
<? } ?>
</table>
{!$p}
<p><input type="submit" class="button" value="Сохранить" /> Все выделенные документы будут удалены</p>
</form>
<? } ?>