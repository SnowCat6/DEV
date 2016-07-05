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
	stack::push($menu);
	ob_start();
}

function endAdmin()
{
	$menu	= stack::pop();
	if (!$menu || !userID()) return ob_end_flush();
	adminMenu::show($menu, ob_get_clean());
}

function module_admin_cache(&$val, &$data)
{
	if (access('clearCache', ''))
		return module('admin:cache', $val, $data);
}
function module_admin_renderEnd($val, &$content)
{
	$adminCtx	= module('page:get', 'adminPanel');
	if (!$adminCtx) return;
	
	$nPos	= strripos($content, '</body');
	if ($nPos > 0) $content = substr_replace($content, $adminCtx, $nPos, 0);
	else $content .= $adminCtx;
}
?>