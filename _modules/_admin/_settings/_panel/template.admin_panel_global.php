<?
function admin_panel_global_update(&$data)
{
	if (!access('write', 'admin:global')) return;

	$globalSettings = getValue('globalSettings');
	if (!is_array($globalSettings)) return;

	$gini		= getGlobalCacheValue('ini');
	mEx('admin:tabUpdate:admin_global', $gini);
	setGlobalIniValues($gini);

	module('message', 'Глобальная конфигурация сохранена');
	m('htaccess');
}
?>
<? function admin_panel_global($ini)
{
	if (!access('write', 'admin:global')) return;
	
	$gini		= getGlobalCacheValue('ini');
	m('script:ajaxForm');
?>
<form action="{{url:admin_toolbar}}" method="post" class="admin ajaxFormNow">
{{admin:tab:admin_global=$gini}}
</form>

<? return 'Глобальные настройки'; } ?>