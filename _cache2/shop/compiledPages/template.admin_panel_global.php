<?
function canAccessGlobalSettings()
{
	if (!hasAccessRole('developer')) return;
	
	$gini			= getGlobalCacheValue('ini');
	$globalAccessIP	= $gini[':']['globalAccessIP'];
	if (GetIntIP($globalAccessIP) == 0) return true;
	
	return $globalAccessIP == GetStringIP(userIP());
}
function admin_panel_global_update(&$data)
{
	if (!canAccessGlobalSettings()) return;

	if (is_array($globalSettings = getValue('globalSettings')))
	{
		$htaccess	= getValue('globalSettingsHtaccess');
		if ($htaccess && testValue('htaccessOverride')){
			file_put_contents_safe('.htaccess', $htaccess);
		}
		
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
		$ini[':memcache'] 		= $globalSettings[':memcache'];
		$ini[':']['useCache']	= $globalSettings[':']['useCache'];
		$ini[':']['compress']	= $globalSettings[':']['compress'];
		$ini[':']['globalRootURL'] 	= $globalSettings[':']['globalRootURL'];
		$ini[':']['globalAccessIP']	= GetStringIP(GetIntIP($globalSettings[':']['globalAccessIP']));

		setGlobalIniValues($ini);
		htaccessMake();
		module('message', 'Глобальная конфигурация сохранена');
	}
}
?><? function admin_panel_global($ini)
{
	if (!canAccessGlobalSettings()) return;
	m('script:ajaxForm');
?>
<form action="<? module("url:admin_toolbar"); ?>" method="post" class="admin ajaxFormNow">
<?
	$redirect		= '';
	$gini			= getGlobalCacheValue('ini');
	@$stieRedirect	= $gini[':globalSiteRedirect'];
	if (!is_array($stieRedirect)) $stieRedirect = array();
	foreach($stieRedirect as $host => $path){
		$redirect .= "$host=$path\r\n";
	}
	$globalRootURL	= $gini[':']['globalRootURL'];
	if (!$globalRootURL) $globalRootURL = globalRootURL;
	
	$globalAccessIP	= $gini[':']['globalAccessIP'];
?>
<div id="globalSettingsTab" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#globalSettings">Основные настройки</a></li>
    <li class="ui-corner-top"><a href="#globalRedirect">Сайты и редиректы</a></li>
    <li class="ui-corner-top"><a href="#globalHhaccess">.htaccess</a></li>
    <li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="globalSettings" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td><label for="globalSiteUseCache">Глобальный кеш</label></td>
    <td>
<input type="hidden" name="globalSettings[:][useCache]" value="" />
<input type="checkbox" name="globalSettings[:][useCache]" id="globalSiteUseCache" value="1"<?= @$gini[':']['useCache']?' checked="checked"':'' ?> />
      </td>
    <td nowrap="nowrap">Глобальный URL сайта</td>
    <td><input type="text" name="globalSettings[:][globalRootURL]" class="input w100" value="<? if(isset($globalRootURL)) echo htmlspecialchars($globalRootURL) ?>" /></td>
  </tr>
  <tr>
    <td><label for="globalSiteUseCompress">Глобальное сжатие страниц</label></td>
    <td>
<input type="hidden" name="globalSettings[:][compress]" value="" />
<input type="checkbox" name="globalSettings[:][compress]" id="globalSiteUseCompress" value="gzip"<?= @$gini[':']['compress']=='gzip'?' checked="checked"':'' ?> />
      </td>
    <td>&nbsp;</td>
    <td><em>Пример: &quot;/&quot; или &quot;/dev&quot;</em></td>
    </tr>
  <tr>
    <td>
<? if (class_exists('Memcache', false)){ ?>
    <label for="globalSiteUseMemcache">Задействовать Memcached</label>
<? }else{ ?>
    <label for="globalSiteUseMemcache"><s>Задействовать Memcached</s></label>
<? } ?>
    </td>
    <td>
<input type="hidden" name="globalSettings[:memcache][server]" value="" />
<input type="checkbox" name="globalSettings[:memcache][server]" id="globalSiteUseMemcache" value="127.0.0.1"<?= $gini[':memcache']['server']?' checked="checked"':'' ?> />
    </td>
    <td nowrap="nowrap">Глоабльный доступ только с IP</td>
    <td><input type="text" name="globalSettings[:][globalAccessIP]" class="input w100" value="<? if(isset($globalAccessIP)) echo htmlspecialchars($globalAccessIP) ?>" /></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td nowrap="nowrap">Ваш текущий IP</td>
    <td><i><?= GetStringIP(userIP())?></i></td>
  </tr>
</table>
</div>

<div id="globalRedirect" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div>Адреса и хосты: вы сейчас на <b><?= htmlspecialchars($_SERVER['HTTP_HOST'])?></b>, правило обработки<strong> HOST_NAME=локальное имя сайта</strong>. <br />
  Если<strong>локальное имя сайта</strong> начинается с <strong>http://</strong>, то выполнится редирект по указанному адресу. <br />
  К примеру: .<strong>*=http://mysite.ru</strong></div>
<div><textarea name="globalSettings[:globalSiteRedirect]" cols="" class="input w100" rows="15"><? if(isset($redirect)) echo htmlspecialchars($redirect) ?></textarea></div>
</div>

<div id="globalHhaccess" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div align="right"><label><input type="checkbox" name="htaccessOverride" value="yes" />Перезаписать .htaccess</label></div>
<div><textarea name="globalSettingsHtaccess" disabled="disabled" class="input w100" rows="15"><?= htmlspecialchars(file_get_contents('.htaccess'))?></textarea></div>
</div>

</div>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$("#globalSettingsTab").tabs();
	$("[name=htaccessOverride]").change(function(){
		if ($(this).attr("checked")){
			$("[name=globalSettingsHtaccess]").removeAttr("disabled");
		}else{
			$("[name=globalSettingsHtaccess]").attr("disabled", "disabled");
		}
	});
});
</script>
<? return '9-Глобальные настройки'; } ?>