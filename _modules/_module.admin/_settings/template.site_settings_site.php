<? function site_settings_site($ini){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
<table border="0" cellspacing="0" cellpadding="2">
<tr>
    <th nowrap="nowrap"><label for="siteUseCache">Использовать кеш</label></th>
    <td>
    <input type="hidden" name="settings[:][useCache]" value="" />
    <input type="checkbox" name="settings[:][useCache]" id="siteUseCache" value="1"<?= @$ini[':']['useCache']?' checked="checked"':'' ?> />
    </td>
    <th nowrap="nowrap">URL сайта</th>
    <td nowrap="nowrap">httP://<input type="text" name="settings[:][url]" class="input" value="{$ini[:][url]}"></td>
    </tr>
<tr>
  <th nowrap="nowrap"><label for="siteUseCompress">Использовать сжатие страниц</label></th>
  <td>
    <input type="hidden" name="settings[:][compress]" value="" />
    <input type="checkbox" name="settings[:][compress]" id="siteUseCompress" value="gzip"<?= @$ini[':']['compress']=='gzip'?' checked="checked"':'' ?> />
  </td>
  <th nowrap="nowrap">Вы на сайте</th>
  <td nowrap="nowrap"><b>http://<?= $_SERVER['HTTP_HOST']?></b></td>
  </tr>
<tr>
  <th nowrap="nowrap"><label for="siteUnionCSS">Объеденять CSS файлы</label></th>
    <td>
    <input type="hidden" name="settings[:][unionCSS]" value="" />
    <input type="checkbox" name="settings[:][unionCSS]" id="siteUnionCSS" value="yes"<?= @$ini[':']['unionCSS']=='yes'?' checked="checked"':'' ?> />
    </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
<tr>
  <th nowrap="nowrap"><label for="siteUnionJScript">Объеденять JavaScript файлы</label></th>
  <td>
    <input type="hidden" name="settings[:][unionJScript]" value="" />
    <input type="checkbox" name="settings[:][unionJScript]" id="siteUnionJScript" value="yes"<?= @$ini[':']['unionJScript']=='yes'?' checked="checked"':'' ?> />
  </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  </tr>
</table>
    </td>
    <td width="33%" valign="top" nowrap="nowrap">
<table border="0" cellspacing="0" cellpadding="0">
<?
$gIni	= getGlobalCacheValue('ini');
$gDb	= $gIni[':db'];
if (!$gDb) $gDb = array();

$db		= $ini[':db'];
if (!$db) $db = array();
$names	= explode(',', 'host,db,prefix,login,passw');
foreach($names as $name){
	$val	= htmlspecialchars($db[$name]);
	if ($name == 'passw') $val = '***';
	if (!$val && $name == 'prefix'){
		$d	= new dbRow();
		$val = $d->dbLink->dbTablePrefix();
	}
	if ($val){
		$val = "<b>$val</b>";
	}else{
		$val	= htmlspecialchars($gDb[$name]);
		if ($val) $val = "<u>$val</u>";
	}
?>
<tr>
    <th>{$name}:</th>
    <td>{!$val}</td>
</tr>
<? } ?>
</table>
    </td>
    <td width="33%" align="right" valign="top"><table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <th align="right" valign="top" nowrap="nowrap">Стиль диалогов</th>
        <td valign="top" nowrap="nowrap">
  <select name="settings[:][jQueryUI]" class="input">
<?
$jQuery		= getCacheValue('jQuery');
$ver		= $jQuery['jQueryUIVersion'];
$styleBase	= cacheRootPath."/script/$ver/css";
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