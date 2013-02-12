<?
function order_update($db, $val, $order){
	if (!is_array($order)) return;
	
	@$fio	= trim($order['name']);
	if (!$fio) return module('message:error', 'Укажите ваше Ф.И.О');
	
	@$phone	= trim($order['phone']);
	@$mail	= trim($order['email']);
	
	if (!$phone && !$mail) return module('message:error', 'Укажите ваш телефон или E-mail');
	
	$d				= array();
	
	$search		= array();
	$search[]	= $order['name'];
	if (@$order['phone'])	$search[] = $order['phone'];
	if (@$order['email'])	$search[] = $order['email'];
	$d['searchField']	= implode(', ', $search);
	
	//	bask
	$bask	= $order['bask'];
	$ddb	= module('doc');
	$order['dbBask']	= array();
	$order['totalPrice']= 0;
	
	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);

	$sql	= array();
	doc_sql(&$sql, $s);
	
	$ddb->open($sql);
	while($data = $ddb->next())
	{
		$id	= $ddb->id();
		$data['orderCount']		= (int)$bask[$id];
		$data['orderPrice']		= docPrice($data);
		$order['dbBask'][$id]	= $data;

		$order['totalPrice']	+=$data['orderCount']*$data['orderPrice'];
	}
	
	$d['orderData']	= $order;
	$d['orderDate']	= makeSQLDate(mktime());
	
	$iid = $db->update($d);
	if (!$iid) return module('message:error', 'Ошибка записи в базу данных');
	
	return $iid;
} ?>