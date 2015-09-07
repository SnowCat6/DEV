<?
function admin_clearCacheCode()
{
	if (!hasAccessRole('developer')) return;
	m('page:title', 'Обновление кода сайтов');
	m('ajax:template', 'ajax_dialogMessage');
	m('message', 'Кеш кода cайтов обновлен.');

	$exec	= new systemExec();	
	$site	= siteFolder();

	delTree(globalCacheFolder, true, true);
	
	systemExec::execPHPscript("index.php clearCacheCode $site");
} ?>