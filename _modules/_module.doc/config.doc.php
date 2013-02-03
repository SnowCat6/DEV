<?
addUrl('page(\d+)', 		'doc:page');
addUrl('page_edit_(\d+)', 	'doc:edit');

addUrl('page_add_(\d+)', 	'doc:add');
addUrl('page_add', 			'doc:add');

addUrl('page_all_(\w+)',	'doc:all');
addUrl('page_all',			'doc:all');

addEvent('document.compile','doc_compile');

addAccess('doc:(\d+)',			'doc_access');
addAccess('doc:(\d+):(\w+)',	'doc_access');
addAccess('doc:add:(\w+)',		'doc_add_access');
addAccess('doc:add:(\w+):(\w+)','doc_add_access');

addEvent('config.end',	'doc_config');

function module_doc_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$documents_tbl['doc_type']= array('Type'=>'enum(\'page\',\'article\',\'catalog\',\'product\',\'comment\')', 'Null'=>'NO', 'Key'=>'', 'Default'=>'page', 'Extra'=>'');
	$documents_tbl['title']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['document']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['originalDocument']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['fields']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['fieldsThis']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['datePublish']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0000-00-00 00:00:00', 'Extra'=>'');
	$documents_tbl['deleted']= array('Type'=>'int(8)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$documents_tbl['visible']= array('Type'=>'int(8)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'1', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
}
?>