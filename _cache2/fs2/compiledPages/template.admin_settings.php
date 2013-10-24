<?
function admin_settings($val, &$data)
{
	if (!hasAccessRole('admin,developer')) return;
	
	$ini	= getCacheValue('ini');
	if (is_array($settings = getValue('settings')))
	{
		$ini	= getCacheValue('ini');
		dataMerge($settings, $ini);
		moduleEx('admin:tabUpdate:site_settings', $settings);
		setIniValues($settings);
		$ini = $settings;
		
		module('message', 'Конфигурация сохранена');
	}
	module('script:ajaxForm');
?><? $module_data = array(); $module_data[] = "Настройки сервера"; moduleEx("page:title", $module_data); ?>
<form action="<? module("getURL:admin_settings"); ?>" method="post" class="admin ajaxFormNow ajaxReload">
<? moduleEx('admin:tab:site_settings', $ini)?>
</form>
<? } ?>