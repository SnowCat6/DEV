<?
addURL('snippets_all',		'snippets:all');
addEvent('document.compile','snippets:compile_doc');

addEvent('admin.tools.settings',	'snippets:toolsPanel');
addAccess('snippets:(.*)',			'snippets_access');

addEvent('page.compile',	'snippets_compile');
function module_snippets_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	//	[[название сниплета]] => {\{модуль}\}
	$thisPage	= preg_replace_callback('#\[\[([^\]]+)\]\]#u', 'parsePageSnippletsFnInit', $thisPage);
}
function parsePageSnippletsFnInit($matches)
{
	$baseCode	= $matches[1];
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	$code		= $snippets[$baseCode];
	if ($code) return $code;

	@$snippets	= getCacheValue('localSnippets');
	return @$snippets[$baseCode];
}
?>
