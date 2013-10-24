<?
function module_order($fn, &$data){
	//	База данных 
	$db 		= new dbRow('order_tbl', 'order_id');
	$db->url 	= 'order';
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("order_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function makeOrderSearchField(&$orderData)
{
	$search = array();
	foreach($orderData as $type => $val){
		$search[] = implode(' ', $val);
	}
	return implode(' ', $search);
}
?><? function order_tools($db, $val, &$data){
	$data['Заказы#ajax']	= getURL('order_all');
} ?>
