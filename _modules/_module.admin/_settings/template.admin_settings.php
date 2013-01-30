<?
function admin_settings($val, &$data)
{
	$bAjax = testValue('ajax');
	if (is_array($settings = getValue('settings'))){
		setIniValues($settings);
		if ($bAjax) return module('message', 'Конфигурация сохранена');
	}
	$ini = getCacheValue('ini');
	module('script:ajaxForm');
?>
<form action="<?= getURL('admin_settings', $bAjax?'ajax':'')?>" method="post" class="adminForm ajaxForm">
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
<div align="right"><input name="Submit" type="submit" value="Сохранить настройки" class="ui-button ui-widget ui-state-default ui-corner-all" /></div>
</form>
<? } ?>