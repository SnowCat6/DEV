<?
function admin_clearCacheCode()
{
	if (!hasAccessRole('developer')) return;
	m('page:title', 'Обновление кода сайтов');
	m('ajax:template', 'ajax_dialogMessage');
	m('message', 'Кеш кода cайтов обновлен.');
	
	delTree(globalCacheFolder, true, true);
	$site	= siteFolder();
	$msg	= execPHP("index.php clearCacheCode $site");
} ?>