<?
addUrl('page(\d+)', 		'doc:page');
addUrl('page_edit_(\d+)', 	'doc:edit');

addUrl('page_add_(\d+)', 	'doc:add');
addUrl('page_add', 			'doc:add');

addUrl('page_all_(\w+)',	'doc:all');
addUrl('page_all',			'doc:all');

addEvent('document.compile','doc_compile');

addAccess('doc:(\d+)',		'doc_access');
addAccess('doc:(\w+)',		'doc_add_access');
addAccess('doc:(\w+):(\w+)','doc_add_access');
?>