<?
function module_price($fn, &$data){
	//	База данных пользователей
	$db = new dbRow('price_tbl', 'price_id');
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}
}
?>