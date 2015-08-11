<?
addUrl('admin_holderEdit',			'holderAdmin:uiEdit');
addUrl('admin_holderWidgetEdit',	'holderAdmin:uiWidgetEdit');
addUrl('admin_holderMode',			'holderAdmin:uiMode');

addUrl('admin_widgetLoad',			'holderAdmin:widgetLoad');
addUrl('ajax_widget_add',			'holderAdmin:ajaxWidgetAdd');
addUrl('ajax_widget_sort',			'holderAdmin:ajaxWidgetSort');
//	Инстументы для административной панели
addEvent('admin.tools.settings',	'holderAdmin:tools');

addAccess('holder:(.*)',	'holderAccess');
?>