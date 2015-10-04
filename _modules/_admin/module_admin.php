<?
function module_admin($fn, &$data)
{
	if ($fn == 'toolbar') event('site.admin', $data);
	if (!userID()) return;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, $data):NULL;
}

function module_access($access, $data){
	list($access,) = explode(':', $access, 2);
	return hasAccessRole($access);
}

function beginAdmin($menu)
{
	if (!userID()) $menu = array();
	stack::push($menu);
	ob_start();
}

function endAdmin()
{
	$menu	= stack::pop();
	if (!$menu) return ob_end_flush();
	
	$menu[':layout'] 	= ob_get_clean();
	module('admin:edit', $menu);
}

function module_admin_cache(&$val, &$data)
{
	if (access('clearCache', ''))
		return module('admin:cache', $val, $data);
}
?>