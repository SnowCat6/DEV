<? function site_settings_site($ini){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" valign="top">
<table border="0" cellspacing="0" cellpadding="2">
<tr>
    <td><label for="siteUseCache">Использовать кеш</label></td>
    <td>
    <input type="hidden" name="settings[:][useCache]" value="" />
    <input type="checkbox" name="settings[:][useCache]" id="siteUseCache" value="1"<?= @$ini[':']['useCache']?' checked="checked"':'' ?> />
    </td>
    <td>URL сайта</td>
    <td>httP://<input type="text" name="settings[:][url]" class="input" value="{$ini[:][url]}"></td>
</tr>
<tr>
  <td><label for="siteUseCompress">Использовать сжатие страниц</label></td>
  <td>
    <input type="hidden" name="settings[:][compress]" value="" />
    <input type="checkbox" name="settings[:][compress]" id="siteUseCompress" value="gzip"<?= @$ini[':']['compress']=='gzip'?' checked="checked"':'' ?> />
  </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
<tr>
  <td><label for="siteUnionCSS">Объеденять CSS файлы</label></td>
    <td>
    <input type="hidden" name="settings[:][unionCSS]" value="" />
    <input type="checkbox" name="settings[:][unionCSS]" id="siteUnionCSS" value="yes"<?= @$ini[':']['unionCSS']=='yes'?' checked="checked"':'' ?> />
    </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
<tr>
  <td><label for="siteUnionJScript">Объеденять JavaScript файлы</label></td>
  <td>
    <input type="hidden" name="settings[:][unionJScript]" value="" />
    <input type="checkbox" name="settings[:][unionJScript]" id="siteUnionJScript" value="yes"<?= @$ini[':']['unionJScript']=='yes'?' checked="checked"':'' ?> />
  </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
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
$jQuery		= getCacheValue('jQuery');
$ver		= $jQuery['jQueryUIVersion'];
$styleBase	= localCacheFolder."/siteFiles/script/$ver/css";
@$thisValue	= $ini[':']['jQueryUI'];
if (!$thisValue) $thisValue = $jQuery['jQueryUIVersionTheme'];
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