<?
include_once  "_class/class.snippets.php";
include_once  "_class/class.snippetsWrite.php";
	
addURL('snippets_all',		'snippets:all');

addEvent('admin.tools.edit','snippets:toolsPanel');
addAccess('snippets:(.*)',	'snippets_access');

addEvent('document.compile',	'snippets:compile');
addEvent('page.compile:before',	'snippets_compile');
function module_snippets_compile($val, &$ev)
{
	$ev['content']	= snippets::compile($ev['content']);
}
?>
