<?
addURL('snippets_all',		'snippets:all');
addEvent('document.compile','snippets:compile');
addEvent('page.compile',	'snippets:compile');

addEvent('admin.tools.settings',	'snippets:toolsPanel');
addAccess('snippets:(.*)',			'snippets_access');
?>
