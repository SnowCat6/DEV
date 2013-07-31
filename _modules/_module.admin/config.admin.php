<?
addUrl('admin_settings',	'admin:settings');
addUrl('admin_toolbar',		'admin:toolbar');
addUrl('admin_SEO',			'admin:SEO');
addUrl('admin_Info',		'admin:info');

addEvent('admin.tools.settings','admin:tools');
addAccess('admin:(.*)',			'admin_access');

addEvent('site.start',		'admin_cache');
?>