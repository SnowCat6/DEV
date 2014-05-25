<?
addUrl('import', 			'import:ui');
addUrl('import_log',		'import:log');
addUrl('import_commit',		'import:commit');

addEvent('admin.tools.edit','import:tools');

addEvent('config.end',	'import_config');
function module_import_config($val, $data)
{
	$import_tbl = array();
	$import_tbl['import_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$import_tbl['article']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$import_tbl['doc_type']= array('Type'=>'enum(\'catalog\',\'product\',\'comment\')', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$import_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$import_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$import_tbl['fields']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$import_tbl['price']= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('import_tbl', $import_tbl);
	
	m('cron:add:Обновление прайсов', 'import:cron');
}
?>