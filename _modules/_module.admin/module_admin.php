<?
function module_admin(&$fn, &$data)
{
	if (!access('write', '')) return;

	noCache();

	if (testValue('clearCache') && access('clearCache', ''))
	{
		clearCache();
		module('doc:recompile');
	}
	if (testValue('recompileDocuments') && access('clearCache', '')) module('doc:recompile');

	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function startDrop($search, $template = ''){
	if (!$search || testValue('ajax')) return;
	$rel = makeQueryString($search, 'data');
	echo "<div class=\"droppable\" rel=\"$rel&template=$template\">";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	echo "</div>";
}
?>