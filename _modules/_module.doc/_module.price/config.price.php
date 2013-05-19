<?
addEvent('doc.update:add',	'price:update');
addEvent('doc.update:edit',	'price:update');
addEvent('doc.sql',			'price_sql');

$ini		= getCacheValue('ini');
@$docPrice	= $ini[':prices'];
if (!$docPrice || !is_array($docPrice)){
	$docPrice = array();
	$docPrice[1000]		= 'до 1000';
	$docPrice[5000]		= 'от  1000 -  5000';
	$docPrice[10000]	= 'от  5000 - 10000';
	$docPrice[20000]	= 'от 10000 - 20000';
	$docPrice[50000]	= 'от 20000';
}
setCacheValue('docPrice', $docPrice);

addEvent('config.end',	'price_config');
function module_price_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price']		= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0.00', 'Extra'=>'');
	$documents_tbl['price_old']	= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
}
?>