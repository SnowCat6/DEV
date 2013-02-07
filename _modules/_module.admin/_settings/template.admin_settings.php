<?
function admin_settings($val, &$data)
{
	if (!hasAccessRole('admin,developer')) return;
	
	$bAjax = testValue('ajax');
	if (is_array($settings = getValue('settings'))){
		setIniValues($settings);
		if ($bAjax) return module('message', 'Конфигурация сохранена');
	}
	$ini = getCacheValue('ini');
	module('script:ajaxForm');
?>
<form action="{{getURL:admin_settings}}" method="post" class="adminForm ajaxFormNow">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
<table border="0" cellspacing="0" cellpadding="2">
<tr>
    <td valign="top"><label for="siteUseCache">Использовать кеш</label></td>
    <td valign="top">
    <input type="hidden" name="settings[:][useCache]" value="" />
    <input type="checkbox" name="settings[:][useCache]" id="siteUseCache" value="1"<?= @$ini[':']['useCache']?' checked="checked"':'' ?> />
    </td>
</tr>
<tr>
  <td valign="top"><label for="siteUseCompress">Использовать сжатие страниц</label></td>
  <td valign="top">
    <input type="hidden" name="settings[:][compress]" value="" />
    <input type="checkbox" name="settings[:][compress]" id="siteUseCompress" value="gzip"<?= @$ini[':']['compress']=='gzip'?' checked="checked"':'' ?> />
  </td>
</tr>
</table>
    </td>
    <td width="33%" valign="top"><table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td valign="top">Стиль диалогов</td>
        <td valign="top" nowrap="nowrap">
<select name="settings[:][jQueryUI]" class="input">
<?
$ver		= getCacheValue('jQueryUIVersion');
$styleBase	= localHostPath."/script/$ver/css";
@$thisValue	= $ini[':']['jQueryUI'];
foreach(getDirs($styleBase) as $name=>$path){
	$class	= $name == $thisValue?' selected="selected"':'';
?>
<option value="{$name}"{!$class}>{$name}</option>
<? } ?>
</select>
		</td>
      </tr>
    </table></td>
    <td width="33%" valign="top">&nbsp;</td>
  </tr>
</table>
<div align="right"><input name="Submit" type="submit" value="Сохранить настройки" class="ui-button ui-widget ui-state-default ui-corner-all" /></div>
</form>
<? } ?>