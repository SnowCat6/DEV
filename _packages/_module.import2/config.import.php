<?
addUrl('import', 			'import:ui');
addUrl('import_import',		'import:ui');
addUrl('import_commit',		'import:ui');
addUrl('import_commit_get',	'import:commit:get');
addUrl('import_commit_set',	'import:commit:set');
addUrl('import_synch',		'import:ui');
addUrl('import_export',		'import:ui');
addUrl('import_log',		'import:log');

addEvent('admin.tools.edit','import:tools');

addEvent('config.end',	'import_config');
function module_import_config($val, $data)
{
	$import_tbl = array();
	$import_tbl['import_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$import_tbl['article']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$import_tbl['doc_type']= array('Type'=>'enum(\'catalog\',\'product\',\'comment\')', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$import_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$import_tbl['parent_doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$import_tbl['name']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$import_tbl['fields']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$import_tbl['date']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$import_tbl['ignore']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$import_tbl['delete']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$import_tbl['updated']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	dbAlterTable('import_tbl', $import_tbl);

	$documents_tbl = array();
	$documents_tbl['importArticle']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$fields	= dbAlterTable('documents_tbl', $documents_tbl);
	
	m('cron:add:Обновление прайсов', 'import:cron');
}
?>