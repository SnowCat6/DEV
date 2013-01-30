<?
function module_admin(&$fn, &$data)
{
	noCache();
	if (!access('write', '')) return;
	if (testValue('clearCache') && access('clearCache', '')) clearCache();

	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, &$data):NULL;
}
?>