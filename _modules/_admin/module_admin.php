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

function beginAdmin($menu, $bTop = true)
{
	if (!userID()) $menu = array();
	if ($menu)	$menu[':useTopMenu']= "$bTop" != 'bottom';
	pushStackName('adminMenu', $menu);
	ob_start();
}

function endAdmin($bMode = NULL)
{
	$menu = getStackData();
	popStackName();
	
	if (!$menu) return ob_end_flush();
	$menu[':layout'] 	= ob_get_clean();
	moduleEx('admin:edit', $menu);
}

function module_admin_cache(&$val, &$data)
{
	if (access('clearCache', ''))
		return module('admin:cache', $val, $data);
}
?>