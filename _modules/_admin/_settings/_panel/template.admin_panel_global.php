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
		
		@$redirect	= explode("\r\n", getValue('globalSiteRedirect'));
		$ini[':globalSiteRedirect'] = array();
		foreach($redirect as $row){
			$row	= explode('=', $row);
			@$host	= trim($row[0]);
			@$path	= trim($row[1]);
			if (!$host || !$path) continue;
			$ini[':globalSiteRedirect'][$host] = $path;
		}
		foreach($globalSettings as $name=>$val){
			$ini[$name]	= $val;
		}
		$ini[':']['globalAccessIP']	= GetStringIP(GetIntIP($globalSettings[':']['globalAccessIP']));

		setGlobalIniValues($ini);
		module('message', 'Глобальная конфигурация сохранена');
		m('htaccess');
	}
}
?>
<? function admin_panel_global($ini)
{
	if (!canAccessGlobalSettings()) return;
	m('script:ajaxForm');
?>
<form action="{{url:admin_toolbar}}" method="post" class="admin ajaxFormNow">
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
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#globalSettings">Основные настройки</a></li>
    <li class="ui-corner-top"><a href="#globalRedirect">Сайты и редиректы</a></li>
    <li class="ui-corner-top"><a href="#globalHhaccess">.htaccess</a></li>
    <li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="globalSettings" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
<div>
    <label>
        <input type="hidden" name="globalSettings[:][useCache]" value="" />
        <input type="checkbox" name="globalSettings[:][useCache]" id="globalSiteUseCache" value="1"<?= @$gini[':']['useCache']?' checked="checked"':'' ?> />
        Глобальный кеш
    </label>
</div>    
    
<div>
    <label>
        <input type="hidden" name="globalSettings[:memcache][server]" value="" />
        <input type="checkbox" name="globalSettings[:memcache][server]" id="globalSiteUseMemcache" value="127.0.0.1"<?= $gini[':memcache']['server']?' checked="checked"':'' ?> />
        
        <? if (class_exists('Memcache', false)){ ?>
            Задействовать Memcache
        <? }else{ ?>
            <s>Задействовать Memcache</s>
        <? } ?>
    </label>
</div>

<div>
<? if (function_exists('fastcgi_finish_request')){ ?>
    <label for="globalSiteUseFinishRequest">Задействовать fastcgi_finish_request</label>
<? }else{ ?>
    <label for="globalSiteUseFinishRequest"><s>Задействовать fastcgi_finish_request</s></label>
<? } ?>
</div>

<div>
    {{admin:menu:admin.tools.global}}
</div>
    </td>
    <td width="33%" valign="top">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <td nowrap="nowrap"><label title="Пример: &quot;/&quot; или &quot;/dev&quot;">Глобальный URL сайта</label></td>
    <td nowrap="nowrap"><input name="globalSettings[:][globalRootURL]" type="text" class="input w100"  value="{$globalRootURL}" /></td>
    </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td nowrap="nowrap"><em>Пример: &quot;/&quot; или &quot;/dev&quot;</em></td>
    </tr>
  <tr>
    <td nowrap="nowrap">Глобальный доступ только с IP</td>
    <td nowrap="nowrap"><input type="text" name="globalSettings[:][globalAccessIP]" class="input w100" value="{$globalAccessIP}" /></td>
    </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td nowrap="nowrap">Ваш текущий IP <i><?= GetStringIP(userIP())?></i></td>
    </tr>
</table>
	</td>
    <td width="33%" align="right" valign="top">
<p><a href="{{url:admin_cacheLog}}" id="ajax">Объекты кеша</a></p>
<p><a href="{{url:admin_SQLquery}}" id="ajax">Выполнить SQL</a></p>
    </td>
  </tr>
</table>

</div>

<div id="globalRedirect" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">
<div>Адреса и хосты: вы сейчас на <b><?= htmlspecialchars($_SERVER['HTTP_HOST'])?></b>, правило обработки<strong> HOST_NAME=локальное имя сайта</strong>. <br />
Если<strong>локальное имя сайта</strong> начинается с <strong>http://</strong>, то выполнится редирект по указанному адресу. <br />
К примеру: .<strong>*=http://mysite.ru</strong></div>
<textarea name="globalSiteRedirect" cols="" class="input w100" rows="15">{$redirect}</textarea>
    </td>
    <td valign="top" style="padding-left:20px"><?
$files	= getDirs(sitesBase);
foreach($files as $name){
	$name	= basename($name);
?>
<div>{$name}</div>
<? } ?>
	</td>
  </tr>
</table>

<div></div>
</div>

<div id="globalHhaccess" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div align="right"><label><input type="checkbox" name="htaccessOverride" value="yes" />Перезаписать .htaccess</label></div>
<div><textarea name="globalSettingsHtaccess" rows="15" readonly class="input w100" id="globalSettingsHtaccess"><?= htmlspecialchars(file_get_contents('.htaccess'))?></textarea></div>
</div>

</div>
</form>
{{script:adminTabs}}
<script language="javascript" type="text/javascript">
$(function(){
	$("[name=htaccessOverride]").change(function(){
		$("#globalSettingsHtaccess").prop("readonly", $(this).attr("checked")?false:true);
	});
});
</script>
<? return 'Глобальные настройки'; } ?>