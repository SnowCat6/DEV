<? function mail_all($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,writer,manager')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');
	
	if (is_array($ids = getValue('mailDelete'))){
		$db->delete($ids);
	}

	$db->order = 'dateSend DESC';
	$db->open();
	$p = dbSeek($db, 15);
?>
<? module("page:style", 'admin.css') ?>
<? module("page:style", 'baseStyle.css') ?>
<? $module_data = array(); $module_data[] = "Отправленные письма"; moduleEx("page:title", $module_data); ?>
<? if(isset($p)) echo $p ?>
<form action="<? module("getURL:admin_mail"); ?>" method="post" class="admin ajaxFormNow ajaxReload">
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
<tr <? if(isset($class)) echo $class ?>>
    <td nowrap>
    <input name="mailDelete[]" type="checkbox" value="<? if(isset($id)) echo htmlspecialchars($id) ?>" />
    <a href="<? module("getURL:admin_mail$id"); ?>" id="ajax"><? if(isset($date)) echo htmlspecialchars($date) ?></a>
    </td>
    <td><? if(isset($data["to"])) echo htmlspecialchars($data["to"]) ?></td>
    <td><? if(isset($data["subject"])) echo htmlspecialchars($data["subject"]) ?></td>
</tr>
<? } ?>
</table>
<? if(isset($p)) echo $p ?>
<p><input type="submit" class="button" value="Сохранить" /> Все выделенные документы будут удалены</p>
</form>
<? } ?>