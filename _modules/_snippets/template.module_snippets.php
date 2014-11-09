<?
function module_snippets($fn, &$data)
{
	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("snippets_$fn");
	return $fn?$fn($val, $data):NULL;
}
function snippets_get($val){
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	if (!is_array($snippets)) $snippets = array();

	$snippets2	= getCacheValue('localSnippets');
	if (!is_array($snippets2)) $snippets2 = array();
	
	return array_merge($snippets, $snippets2);
}
function snippets_visual($val, $data){
	return false;
}
function snippets_compile_doc($val, &$thisPage){
	//	[[название сниплета]] => {\{модуль}\}
	$thisPage	= preg_replace_callback('#\[\[([^\]]+)\]\]#u', 'parsePageSnippletsFn', $thisPage);
}
function parsePageSnippletsFn($matches)
{
	$baseCode	= $matches[1];
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	$code		= $snippets[$baseCode];
	if ($code) return $code;

	@$snippets	= getCacheValue('localSnippets');
	return @$snippets[$baseCode];
}
function snippets_toolsPanel($val, &$data){
	if (!access('write', 'snippets:')) return;
	$data['Сниппеты#ajax']	= getURL('snippets_all');
}
function module_snippets_access($acccess, &$data){
	return hasAccessRole('admin,developer,writer');
}
?>