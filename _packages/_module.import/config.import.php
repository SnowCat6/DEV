<?
addUrl('import', 		'import:ui');
addUrl('import_log',	'import:log');
addEvent('import.xml',	'import:xml1c');

addEvent('config.end',	'import_config');
function module_import_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['importAvalible']= array('Type'=>'int(8) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
	
	m('cron:add:Обновление прайсов', 'price:doImport');
}
?>