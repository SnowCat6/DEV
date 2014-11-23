<?
function admin_clearCacheCode()
{
	if (!hasAccessRole('developer')) return;
	m('page:title', 'Обновление кода сайтов');
	
	delTree(globalCacheFolder, true, true);
	$site	= siteFolder();
	$msg	= execPHP("index.php clearCacheCode $site");
?>
Кеш кода cайтов удален.
<? ; } ?>