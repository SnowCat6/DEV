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

function beginAdmin($menu, $bTop = true){
	if ($menu){
		$menu[':useTopMenu']= $bTop;
	}
	pushStackName('adminMenu', $menu);
	ob_start();
}

function endAdmin($bMode = NULL)
{
	$menu = getStackData();
	popStackName();
	
	if (!$menu) return ob_end_flush();
	
	$menu[':layout'] 	= ob_get_clean();
	if (is_bool($bMode)){
		$menu[':useTopMenu']= $bMode;
	}
	
	setNoCache();
	moduleEx('admin:edit', $menu);
}

function startDrop($search, $template = '', $bSortable = false)
{
	if (!$search || testValue('ajax')) return;
	setNoCache();
	$rel = makeQueryString($search, 'data');
	$class= $bSortable?' class="sortable"':'';
	echo "<div rel=\"droppable:$rel&template=$template\"$class>";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	setNoCache();
	echo "</div>";
}
function module_admin_cache($val, $data)
{
	if (!access('clearCache', '')) return;

	$site	= siteFolder();
	if (testValue('clearCode')){
		$msg	= execPHP("index.php clearCacheCode $site");
		if ($msg) module('message', "Кеш кода очищен.<div>$msg</div>");
		else  module('message', "Ошибка");
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('clearCache')){
		module('doc:clear');
		$msg	= execPHP("index.php clearCache $site");
		if ($msg) module('message', "Кеш очищен. <div>$msg</div>");
		else  module('message', "Ошибка");
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
		m('ajax:template', 'ajax_dialogMessage');
	}else
	if (testValue('clearThumb')){
		clearThumb(images);
		module('doc:clear');
		execPHP("index.php clearCache $site");
		module('message', 'Миниизображения удалены');
		m('ajax:template', 'ajax_dialogMessage');
	};
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