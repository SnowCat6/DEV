<?
function module_admin(&$fn, &$data)
{
	if (!defined('userID')) return;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, $data):NULL;
}

function module_access($access, &$data){
	list($access,) = explode(':', $access, 2);
	return hasAccessRole($access);
}

function beginAdmin(){
	ob_start();
}

function endAdmin($menu, $bTop = true)
{
	if (!$menu) return ob_end_flush();
	
	$menu[':useTopMenu']= $bTop;
	$menu[':layout'] 	= ob_get_clean();
	module('admin:edit', $menu);
}

function startDrop($search, $template = '', $bSortable = false){
	if (!$search || testValue('ajax')) return;
	$rel = makeQueryString($search, 'data');
	$class= $bSortable?' class="sortable"':'';
	echo "<div rel=\"droppable:$rel&template=$template\"$class>";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	echo "</div>";
}
function module_admin_cache($val, $data)
{
	if (!access('clearCache', '')) return;

	if (testValue('clearCode'))
	{
		clearCacheCode();
		module('message', 'Кеш кода очищен.');
	}else
	if (testValue('clearCache'))
	{
		clearCache();
		module('doc:clear');
		module('message', 'Кеш очищен, перезагрузите страницу.');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
	}else
	if (testValue('clearThumb')){
		clearThumb(images);
		clearCache();
		module('doc:clear');
		module('message', 'Миниизображения удалены');
	}
}
function admin_tools($val, &$data){
	if (access('write', 'admin:settings'))	$data[':admin']['Настройки сервера#ajax']	= getURL('admin_settings');
	if (access('write', 'admin:serverInfo'))$data[':admin']['PHP Info']	= getURL('admin_Info');
	if (access('write', 'admin:SEO'))		$data['SEO#ajax']	= getURL('admin_SEO');
}
function admin_toolsService($val, &$data){
	if (!access('clearCache', '')) return;
	$data['Удалить миниизображения#ajax_dialog']= getURL('', 'clearThumb');
	$data['Обновить документы#ajax_dialog']		= getURL('', 'recompileDocuments');
	$data['Удалить кеш#ajax_dialog']	= getURL('', 'clearCache');
	$data['Пересобрать код#ajax_dialog']	= getURL('', 'clearCode');
}
function module_admin_access($access, &$data){
	$tool	= $data[1];
	switch($tool){
		case 'SEO':
			return hasAccessRole('SEO');
		case 'serverInfo':
			return hasAccessRole('developer');
	}
	return hasAccessRole('admin,developer');
}
?>