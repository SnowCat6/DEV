<?
function module_snippets($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("snippets_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function snippets_compile($val, &$data){
	//	[[название сниплета]] => {\{модуль}\}
	$data= preg_replace_callback('#\[\[([^\]]+)\]\]#u', parsePageSnippletsFn, $data);
}
function parsePageSnippletsFn($matches)
{
	$baseCode	= $matches[1];
	$ini		= getCacheValue('ini');
	@$snippets	= $ini[':snippets'];
	return @$snippets[$baseCode];
}
?>