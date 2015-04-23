<?
addUrl('admin_holderEdit',			'holderAdmin:uiEdit');
addUrl('admin_holderWidgetEdit',	'holderAdmin:uiWidgetEdit');
addUrl('admin_holderMode',			'holderAdmin:uiMode');

addUrl('admin_widgetLoad',			'holderAdmin:widgetLoad');
//	Инстументы для административной панели
addEvent('admin.tools.edit',	'holderAdmin:tools');

addAccess('holder:(.*)',	'holderAccess');
?>