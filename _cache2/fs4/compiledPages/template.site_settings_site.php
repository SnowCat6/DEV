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
    <td>httP://<input type="text" name="settings[:][url]" class="input" value="<? if(isset($ini[":"]["url"])) echo htmlspecialchars($ini[":"]["url"]) ?>"></td>
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
  <option value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?></option>
<? } ?>
  </select>
          </td>
        </tr>
    </table></td>
    </tr>
</table>
<? return '1-Настройки сайта'; } ?>