<? function site_settings_site($ini){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
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
    <td valign="top" nowrap="nowrap">
    </td>
    <td valign="top"><table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="right" valign="top" nowrap="nowrap">Стиль диалогов</td>
        <td valign="top" nowrap="nowrap">
  <select name="settings[:][jQueryUI]" class="input">
  <?
$ver		= getCacheValue('jQueryUIVersion');
$styleBase	= localCacheFolder."/siteFiles/script/$ver/css";
@$thisValue	= $ini[':']['jQueryUI'];
if (!$thisValue) $thisValue = getCacheValue('jQueryUIVersionTheme');
foreach(getDirs($styleBase) as $name=>$path){
	$class	= $name == $thisValue?' selected="selected"':'';
?>
  <option value="{$name}"{!$class}>{$name}</option>
<? } ?>
  </select>
          </td>
        </tr>
    </table></td>
    </tr>
</table>
<? return '1-Настройки сайта'; } ?>