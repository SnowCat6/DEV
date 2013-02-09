<?
function admin_settings($val, &$data)
{
	if (!hasAccessRole('admin,developer')) return;
	
	$bAjax = testValue('ajax');
	if (is_array($settings = getValue('settings'))){
		setIniValues($settings);
		if ($bAjax) module('message', 'Конфигурация сохранена');
	}
	$ini	= getCacheValue('ini');
	module('script:ajaxForm');
?>
<form action="{{getURL:admin_settings}}" method="post" class="adminForm ajaxFormNow">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
<table border="0" cellspacing="0" cellpadding="2">
<tr>
    <td width="100%" valign="top"><label for="siteUseCache">Использовать кеш</label></td>
    <td valign="top">
    <input type="hidden" name="settings[:][useCache]" value="" />
    <input type="checkbox" name="settings[:][useCache]" id="siteUseCache" value="1"<?= @$ini[':']['useCache']?' checked="checked"':'' ?> />
    </td>
</tr>
<tr>
  <td width="100%" valign="top"><label for="siteUseCompress">Использовать сжатие страниц</label></td>
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
<?
if (hasAccessRole('developer')){

	if (is_array($globalSettings = getValue('globalSettings')))
	{
		@$redirect = explode("\r\n", $globalSettings[':globalSiteRedirect']);
		$globalSettings[':globalSiteRedirect'] = array();
		foreach($redirect as $row){
			$row	= explode('=', $row);
			@$host	= trim($row[0]);
			@$path	= trim($row[1]);
			if (!$host || !$path) continue;
			$globalSettings[':globalSiteRedirect'][$host] = $path;
		}

		setGlobalIniValues($globalSettings);
		if ($bAjax) module('message', 'Глобальная конфигурация сохранена');
	}

	$redirect		= '';
	$gini			= getGlobalCacheValue('ini');
	@$stieRedirect	= $gini[':globalSiteRedirect'];
	if (!is_array($stieRedirect)) $stieRedirect = array();
	foreach($stieRedirect as $host => $path){
		$redirect .= "$host=$path\r\n";
	}
?>
<tr>
    <td colspan="3" valign="top">&nbsp;</td>
</tr>
<tr>
    <td valign="top"><table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%" valign="top"><label for="globalSiteUseCache">Глобальный кеш</label></td>
        <td valign="top"><input type="hidden" name="globalSettings[:][useCache]" value="" />
          <input type="checkbox" name="globalSettings[:][useCache]" id="globalSiteUseCache" value="1"<?= @$gini[':']['useCache']?' checked="checked"':'' ?> /></td>
      </tr>
      <tr>
        <td width="100%" valign="top"><label for="globalSiteUseCompress">Глобальное сжатие страниц</label></td>
        <td valign="top"><input type="hidden" name="globalSettings[:][compress]" value="" />
          <input type="checkbox" name="globalSettings[:][compress]" id="globalSiteUseCompress" value="gzip"<?= @$gini[':']['compress']=='gzip'?' checked="checked"':'' ?> /></td>
      </tr>
    </table></td>
    <td valign="top">&nbsp;</td>
    <td valign="top">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td valign="top">&nbsp;</td>
    <td valign="top">&nbsp;</td>
  </tr>
<? } ?>
</table>
<? if (hasAccessRole('developer')){ ?>
<div>Адреса и хосты: вы сейчас на <b><?= htmlspecialchars($_SERVER['HTTP_HOST'])?></b></div>
<div><textarea name="globalSettings[:globalSiteRedirect]" cols="" class="input w100" rows="5">{$redirect}</textarea></div>
<? } ?>
<p align="right"><input name="Submit" type="submit" value="Сохранить настройки" class="ui-button ui-widget ui-state-default ui-corner-all" /></p>
</form>
<? } ?>