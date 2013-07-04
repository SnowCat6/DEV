<?
addEvent('doc.update:add',		'price:update');
addEvent('doc.update:edit',		'price:update');
addEvent('doc.sql',				'price_sql');
addEvent('prop.query:price',	'price:query');

addEvent('config.end',	'price_config');
function module_price_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price']		= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0.00', 'Extra'=>'');
	$documents_tbl['price_old']	= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
	
	m('prop:addQuery:price', 'Отбор по цене');
}
?>