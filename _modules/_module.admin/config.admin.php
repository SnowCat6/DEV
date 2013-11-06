<?
addUrl('admin_settings',	'admin:settings');
addUrl('admin_toolbar',		'admin:toolbar');
addUrl('admin_SEO',			'admin:SEO');
addUrl('admin_Info',		'admin:info');
addUrl('admin_cacheLog',	'admin:cacheLog');

addEvent('admin.tools.settings','admin:tools');
addEvent('admin.tools.service',	'admin:toolsService');
addAccess('admin:(.*)',			'admin_access');

addEvent('site.start',		'admin_cache');
?>