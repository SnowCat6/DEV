<?
addEvent('doc.update:add',	'price:update');
addEvent('doc.update:edit',	'price:update');

addEvent('config.end',	'price_config');
function module_price_config($val, $data)
{
	$price_tbl = array();
	$price_tbl['price_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$price_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$price_tbl['price']= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0.00', 'Extra'=>'');
	$price_tbl['priceGroup']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$price_tbl['priceSource']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('price_tbl', $price_tbl);
}
?>