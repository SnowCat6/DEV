<?
addURL('admin_snippets_all',		'snippets:all');
addURL('admin_snippet_edit',		'snippets:edit');

addEvent('admin.tools.edit','snippets:toolsPanel');
addAccess('snippets:(.*)',	'snippets_access');

addEvent('document.compile',	'snippets:compile');
addEvent('page.compile:before',	'snippets_compile');
function module_snippets_compile($val, &$ev)
{
	$ev['content']	= snippets::compile($ev['content']);
}
?>
