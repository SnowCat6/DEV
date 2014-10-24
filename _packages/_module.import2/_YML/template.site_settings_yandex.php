<? function site_settings_yandex_update(&$ini)
{
	m('jq:ajaxLink');
}
function site_settings_yandex($ini){
	if (!hasAccessRole('admin')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="50%" valign="top">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
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
      </td>
      <td width="50%" valign="top"><a href="{{url:yandex-export}}" id="ajax">Создать файл yandex.xml
      </a></td>
    </tr>
  </tbody>
</table>


<? return 'Yandex XML'; } ?>

<?
//	+function import_YandexXMLtools
function import_YandexXMLtools($fn, &$data)
{
	if (!access('add', 'doc:product')) return;
	$data['Создать YandexXML#ajax']	= getURL('yandex-export');
}
?>