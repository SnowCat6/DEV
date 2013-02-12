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
	return $fn?$fn($db, $val, &$data):NULL;
}
function orderSearchField($order)
{
	$search		= array();
	$search[]	= $order['orderData']['name'];
	if (@$order['orderData']['phone'])	$search[] = $order['orderData']['phone'];
	if (@$order['orderData']['email'])	$search[] = $order['orderData']['email'];
	return implode(' ', $search);
}
?>