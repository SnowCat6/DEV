<?
addUrl('import_merlion',		'merlion:catalogs');
addUrl('import_merlion_synch',	'merlion:synchUI');
addUrl('import_merlion_fix',	'merlion:fix');

addEvent('admin.tools.edit',	'merlion:tools');

addEvent('config.end',	'merlion_config');
function module_merlion_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price_merlion']	= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlter::alterTable('documents_tbl', $documents_tbl);
	
	m("cron:add:Обновление Merlion", "merlion:synch");
}
?>