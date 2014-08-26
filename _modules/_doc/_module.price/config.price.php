<?
addEvent('doc.update:add',		'price:update');
addEvent('doc.update:edit',		'price:update');
addEvent('doc.sql',				'price_sql');

addEvent('config.end',	'price_config');
function module_price_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['price']		= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0.00', 'Extra'=>'');
	$documents_tbl['price_old']	= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	dbAlterTable('documents_tbl', $documents_tbl);
	
	addEvent('prop.query:price',	'price:query');
	addEvent('prop.querySQL:price',	'price:querySQL');
	addEvent('prop.queryHelp:price','price:queryHelp');
	m('prop:addQuery:price', 		'Отбор по цене');

	//	Временно тут
	addEvent('prop.query:round',	'price:round');
	addEvent('prop.querySQL:round',	'price:roundSQL');
	addEvent('prop.queryHelp:round','price:roundHelp');
	m('prop:addQuery:round', 		'Диапазоны свойств');

	//	Временно тут
	addEvent('prop.query:parent',	'price:parent');
	addEvent('prop.querySQL:parent','price:parentSQL');
	addEvent('prop.queryHelp:parent','price:parentHelp');
	m('prop:addQuery:parent', 		'Группировка по родителям');
}
?>