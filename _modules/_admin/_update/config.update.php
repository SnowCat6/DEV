<?
setCacheValue('DEV_CMS_VERSION', DEV_CMS_VERSION);

addUrl('admin_update',			'admin:update');
addUrl('admin_update_check',	'admin:update_check');
addUrl('admin_update_download',	'admin:update_download');
addUrl('admin_update_install',	'admin:update_install');
addUrl('server_update_get',		'server_update_get');

addAccess('update', 			'access:developer');
?>
