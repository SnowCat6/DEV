<?
addUrl('admin_holderEdit',			'holderAdmin:uiEdit');
addUrl('admin_holderWidgetEdit',	'holderAdmin:uiWidgetEdit');
addUrl('admin_holderMode',			'holderAdmin:uiMode');

addUrl('admin_widgetLoad',			'holderAdmin:widgetLoad');
addUrl('ajax_widget_add',			'holderAdmin:ajaxWidgetAdd');
addUrl('ajax_widget_sort',			'holderAdmin:ajaxWidgetSort');
//	Инстументы для административной панели
addEvent('admin.tools.settings2',	'holderAdmin:tools');

addAccess('holder:(.*)',	'holderAccess');

addEvent('page.compile:before',	'htmlWidgetCompile');
function module_htmlWidgetCompile($val, &$ev)
{
	include_once "_class/class.widgetCompile.php";

	$thisPage	= &$ev['content'];
	$compiller	= new widgetTagCompile('widget:');
	$thisPage	= $compiller->compile($thisPage);
}
?>