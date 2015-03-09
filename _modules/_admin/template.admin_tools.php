<?
//	+function admin_tools
function admin_tools($val, &$data)
{
	if (access('write', 'admin:settings'))	$data[':admin']['Настройки сервера#ajax_edit']	= getURL('admin_settings');
	if (access('write', 'admin:serverInfo'))$data[':admin']['PHP Info']	= array(
		'href'	=> getURL('admin_Info'),
		'target'=> '_new'
	);
}
//	+function admin_toolsService
function admin_toolsService($val, &$data)
{
	if (!access('clearCache', '')) return;
	$data['Удалить миниизображения#ajax']	= getURL('', 'clearThumb');
	$data['Обновить документы#ajax']		= getURL('', 'recompileDocuments');
	$data['Удалить кеш#ajax']		= getURL('', 'clearCache');
	$data['Пересобрать код#ajax']	= getURL('', 'clearCode');
}
?>
