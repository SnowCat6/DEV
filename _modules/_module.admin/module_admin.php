<?
function module_admin(&$fn, &$data)
{
	if (!defined('userID')) return;
//	if (!access('write', '')) return;

//	noCache();
//	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, $data):NULL;
}

function module_access($access, $data){
	list($access,) = explode(':', $access, 2);
	return hasAccessRole($access);
}

function beginAdmin(){
	ob_start();
}

function endAdmin($menu, $bTop = true){
	$content = ob_get_clean();
	if (!$menu) return print($content);
	$menu[':useTopMenu']= $bTop;
	$menu[':layout'] 	= $content;
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