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
?>