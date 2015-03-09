<?
function admin_clearCacheCode()
{
	if (!hasAccessRole('developer')) return;
	m('page:title', 'Обновление кода сайтов');
	m('ajax:template', 'ajax_dialogMessage');
	m('message', 'Кеш кода cайтов обновлен.');
	
	$fn		= getFn('execPHPscript');
	$site	= siteFolder();

	delTree(globalCacheFolder, true, true);
	
	if ($fn) return $fn("index.php clearCacheCode $site");
//	$msg	= execPHP("index.php clearCacheCode $site");
} ?>