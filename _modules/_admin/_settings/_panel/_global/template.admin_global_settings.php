<? function admin_global_settings_update(&$gini)
{
	$globalSettings = getValue('globalSettings');
	foreach($globalSettings as $name => $val)
	{
		if (!is_array($val)) continue;
		foreach($val as $valueName => $value)
		{
			$gini[$name][$valueName]	= $value;
		}
	}
	$gini[':']['globalAccessIP']	= GetStringIP(GetIntIP($globalSettings[':']['globalAccessIP']));
}?>

<? function admin_global_settings(&$gini)
{
	$setting	= array();
	$settings['Глобальный кеш']	= array(
		'name'	=> 'globalSettings[:][useCache]',
		'value'	=> '1',
		'checked'	=> $gini[':']['useCache']
	);
	$settings['Глобальный кеш']	= array(
		'name'	=> 'globalSettings[:][useCache]',
		'value'	=> '1',
		'checked'	=> $gini[':']['useCache']
	);
	$settings['Задействовать Memcache']	= array(
		'name'	=> 'lobalSettings[:memcache][server]',
		'value'	=> '127.0.0.1',
		'checked'	=> $gini[':memcache']['server'],
		'disable'	=> class_exists('Memcache', false)==false
	);
	$settings['Задействовать fastcgi_finish_request']	= array(
		'disable'	=> function_exists('fastcgi_finish_request')==false
	);


	$globalRootURL	= $gini[':']['globalRootURL'];
	if (!$globalRootURL) $globalRootURL = globalRootURL;
	
	$globalAccessIP	= $gini[':']['globalAccessIP'];
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="33%" valign="top">
{{admin:settingsMenu:admin.settings.global=$settings}}
{{admin:menu:admin.tools.global}}
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
<p><a href="{{url:admin_clearCacheCode}}" id="ajax">Обновить все сайты</a></p>
    </td>
  </tr>
</table>

<? return '1-Основные настройки'; } ?>