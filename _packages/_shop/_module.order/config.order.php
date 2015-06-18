<?
addUrl('order(\d+)',		'order:ordered');
addUrl('order_all',			'order:all');
addUrl('order_edit(\d+)',	'order:edit');
addEvent('admin.tools.edit','order:tools');

addEvent('order.changeStatus',	'order:status');

//	Кассир, может только заказы обрабатывать
addRole('Кассир',		'cashier');
addAccess('adminPanel',	'access:cashier');

$orderTypes = array();
$orderTypes['new']		= 'Новый';
$orderTypes['received']	= 'Обрабатывается';
$orderTypes['delivery']	= 'Доставляется';
$orderTypes['wait']		= 'Ожидает доставки';
$orderTypes['completed']= 'Доставлено';
$orderTypes['rejected']	= 'Удален';
setCacheValue('orderTypes', $orderTypes);

addEvent('config.end',	'order_config');
function module_order_config($val, $data)
{
	$order_tbl = array();
	$order_tbl['order_id']	= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$order_tbl['user_id']	= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$order_tbl['orderDate']	= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$order_tbl['totalPrice']= array('Type'=>'float(10,2) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0.00', 'Extra'=>'');
	$order_tbl['orderStatus']= array('Type'=>'enum(\'new\',\'received\',\'rejected\',\'delivery\',\'wait\',\'completed\')', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'new', 'Extra'=>'');
	$order_tbl['searchField']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$order_tbl['orderNote']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$order_tbl['orderData']= array('Type'=>'array', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$order_tbl['orderBask']= array('Type'=>'array', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$order_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	dbAlterTable('order_tbl', $order_tbl);
}
?>