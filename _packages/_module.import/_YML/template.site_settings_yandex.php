<? function site_settings_yandex_update(&$ini)
{
}
function site_settings_yandex($ini){
	if (!hasAccessRole('admin')) return;
?>
<table width="50%" border="0" cellpadding="2" cellspacing="0">
<tr>
    <td nowrap="nowrap">Название магазина</td>
    <td width="100%"><input type="text" class="input w100" name="settings[:yandex][shopName]" value="{$ini[:yandex][shopName]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Название компании</td>
    <td width="100%"><input type="text" class="input w100" name="settings[:yandex][shopCompany]" value="{$ini[:yandex][shopCompany]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Название агенства техподдержки</td>
    <td width="100%"><input type="text" class="input w100" name="settings[:yandex][shopAgency]" value="{$ini[:yandex][shopAgency]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Эл. адрес техподдержки</td>
    <td width="100%"><input type="text" class="input w100" name="settings[:yandex][shopMail]" value="{$ini[:yandex][shopMail]}" /></td>
</tr>
</table>

<? return 'Yandex XML'; } ?>