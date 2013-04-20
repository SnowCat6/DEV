<?
addEvent('doc.update:add',	'price:update');
addEvent('doc.update:edit',	'price:update');

addEvent('config.end',	'price_config');
function module_price_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price']= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
}
?>