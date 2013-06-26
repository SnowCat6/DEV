<? function admin_panel_global_update($data)
{
	if (is_array($globalSettings = getValue('globalSettings')))
	{
		$ini		= getGlobalCacheValue('ini');
		@$redirect	= explode("\r\n", $globalSettings[':globalSiteRedirect']);
		$ini[':globalSiteRedirect'] = array();
		foreach($redirect as $row){
			$row	= explode('=', $row);
			@$host	= trim($row[0]);
			@$path	= trim($row[1]);
			if (!$host || !$path) continue;
			$ini[':globalSiteRedirect'][$host] = $path;
		}
		$ini[':']['useCache'] = $globalSettings[':']['useCache'];
		$ini[':']['compress'] = $globalSettings[':']['compress'];

		setGlobalIniValues($ini);
		module('message', 'Глобальная конфигурация сохранена');
	}
}
?>
<? function admin_panel_global($ini)
{
	m('script:ajaxForm');
?>
<? if (!hasAccessRole('developer')) return; ?>
<form action="{{url:admin_toolbar}}" method="post" class="admin ajaxFormNow">
<?

	$redirect		= '';
	$gini			= getGlobalCacheValue('ini');
	@$stieRedirect	= $gini[':globalSiteRedirect'];
	if (!is_array($stieRedirect)) $stieRedirect = array();
	foreach($stieRedirect as $host => $path){
		$redirect .= "$host=$path\r\n";
	}
?>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td valign="top"><label for="globalSiteUseCache">Глобальный кеш</label></td>
    <td valign="top"><input type="hidden" name="globalSettings[:][useCache]" value="" />
      <input type="checkbox" name="globalSettings[:][useCache]" id="globalSiteUseCache" value="1"<?= @$gini[':']['useCache']?' checked="checked"':'' ?> /></td>
  </tr>
  <tr>
    <td valign="top"><label for="globalSiteUseCompress">Глобальное сжатие страниц</label></td>
    <td valign="top"><input type="hidden" name="globalSettings[:][compress]" value="" />
      <input type="checkbox" name="globalSettings[:][compress]" id="globalSiteUseCompress" value="gzip"<?= @$gini[':']['compress']=='gzip'?' checked="checked"':'' ?> /></td>
  </tr>
</table><br />
<div>Адреса и хосты: вы сейчас на <b><?= htmlspecialchars($_SERVER['HTTP_HOST'])?></b>, правило обработки HOST_NAME=локальное имя сайта</div>
<div><textarea name="globalSettings[:globalSiteRedirect]" cols="" class="input w100" rows="15">{$redirect}</textarea></div>
<p><input type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></p>
</form>
<? return '9-Глобальные настройки'; } ?>