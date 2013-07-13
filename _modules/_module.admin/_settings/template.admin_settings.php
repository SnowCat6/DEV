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
?>
{{page:title=Настройки сервера}}
<form action="{{getURL:admin_settings}}" method="post" class="admin ajaxFormNow ajaxReload">
<? moduleEx('admin:tab:site_settings', $ini)?>
</form>
<? } ?>