<?
addUrl('admin_settings',	'admin:settings');
addUrl('admin_toolbar',		'admin:toolbar');
addUrl('admin_SEO',			'admin:SEO');
addUrl('admin_Info',		'admin:info');
addUrl('admin_cacheLog',	'admin:cacheLog');
addUrl('admin_SQLquery',	'admin:SQLquery');
addUrl('admin_SQLqueryTables',	'admin:SQLqueryTables');

addEvent('admin.tools.settings','admin:tools');
addEvent('admin.tools.service',	'admin:toolsService');
addAccess('admin:(.*)',			'admin_access');

addEvent('site.start',		'admin_cache');

addEvent('page.compile',	'admin_page_compile');
function module_admin_page_compile($val, &$thisPage)
{
	//	{beginAdmin}  {endAdmin}
	$thisPage	= str_replace('{beginAdmin}',	'<? ob_start() ?>',			$thisPage);
	$thisPage	= str_replace('{endAdmin}',		'<? endAdmin($menu) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminTop}',	'<? endAdmin($menu, true) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminBottom}','<? endAdmin($menu, false) ?>',	$thisPage);
	$thisPage	= preg_replace('#{endAdmin:(\$[\w\d_]+)}#',	'<? endAdmin(\\1) ?>',	$thisPage);

	//	Admin tools
	$thisPage	= str_replace('{head}',		'{{!page:header}}',		$thisPage);
	$thisPage	= str_replace('{admin}',	'{{!admin:toolbar}}',	$thisPage);
}
?>