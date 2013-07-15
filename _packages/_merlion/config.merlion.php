<?
addUrl('import_merlion',		'merlion');
addUrl('import_merlion_synch',	'merlion:synch');

addEvent('admin.tools.edit',	'merlion:tools');

addEvent('config.end',	'merlion_config');
function module_merlion_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price_merlion']	= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
	
	m("cron:add:merlion", "merlion:synch");
}
?>