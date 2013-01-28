<?
function admin_settings($val, &$data){
	if (is_array($settings = getValue('settings'))){
		setIniValues($settings);
	}
	module('script:ajaxForm');
	$ini = getCacheValue('ini');
?>
<form action="<?= getURL('admin_settings')?>" method="post" class="adminForm">
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td valign="top">&nbsp;</td>
    <td valign="top" nowrap="nowrap">&nbsp;</td>
    <td valign="top"><label for="siteUseCache">Использовать кеш</label></td>
    <td valign="top" nowrap="nowrap">
    <input type="hidden" name="settings[:][useCache]" value="0" />
    <input type="checkbox" name="settings[:][useCache]" id="siteUseCache" value="1"<?= @$ini[':']['useCache']?' checked="checked"':'' ?> />
    </td>
  </tr>
</table>
<div align="right"><input name="Submit" type="submit" value="Сохранить настройки" /></div>
</form>
<? } ?>