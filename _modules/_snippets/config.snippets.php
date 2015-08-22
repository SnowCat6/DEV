<?
include_once  "class.snippets.php";
include_once  "class.snippetsWrite.php";
	
addURL('snippets_all',		'snippets:all');
addEvent('document.compile','snippets:compile_doc');

addEvent('admin.tools.edit','snippets:toolsPanel');
addAccess('snippets:(.*)',	'snippets_access');

addEvent('page.compile:before',		'snippets_compile');
function module_snippets_compile($val, &$ev)
{
	$snippets		= new snippets();
	$ev['content']	= $snippets->compile($ev['content']);
}
?>
