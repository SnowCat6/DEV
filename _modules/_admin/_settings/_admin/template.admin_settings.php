<?
function admin_settings($val, &$data)
{
	if (!access('write', 'admin:settings')) return;
	
	$ini	= getCacheValue('ini');
	if (is_array($settings = getValue('settings')))
	{
		$ini	= getCacheValue('ini');
		dataMerge($settings, $ini);
		moduleEx('admin:tabUpdate:site_settings', $settings);
		setIniValues($settings);
		$ini = $settings;
		
		m('message', 'Конфигурация сохранена');
	}
?>
{{script:ajaxForm}}
{{ajax:template=ajax_edit}}
{{page:style=baseStyle.css}}
{{page:title=Настройки сервера}}
<form action="{{getURL:admin_settings}}" method="post" class="admin ajaxFormNow ajaxReload">
<? moduleEx('admin:tab:site_settings', $ini)?>
</form>
<? } ?>