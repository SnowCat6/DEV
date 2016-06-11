<?
function module_snippets($fn, &$data)
{
	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("snippets_$fn");
	return $fn?$fn($val, $data):NULL;
}
 function snippets_visual($val, $data){
	return false;
}
function snippets_compile($val, &$thisPage)
{
	$thisPage	= snippets::compile($thisPage);
}
function snippets_toolsPanel($val, &$data)
{
	$data['Сниппеты#ajax']	= getURL('admin_snippets_all');
}
function module_snippets_access($acccess, $data)
{
	return snippetsWrite::access($acccess);
}
?>