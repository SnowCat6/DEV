<?
function module_admin(&$fn, &$data)
{
	if (!access('write', '')) return;

	noCache();

	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function startDrop($search, $template = ''){
	if (!$search || testValue('ajax')) return;
	$rel = makeQueryString($search, 'data');
	echo "<div rel=\"droppable:$rel&template=$template\">";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	echo "</div>";
}
function module_admin_cache($val, $data)
{
	if (!access('clearCache', '')) return;

	if (testValue('clearCache'))
	{
		clearCache();
		module('doc:recompile');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
	}
}
?>