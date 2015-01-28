<?
function order_add($db, $val, $order)
{
	if (!is_array($order)) return;

	module('feedback');
	$error = checkValidFeedbackForm('order', $order);
	if (is_string($error)){
		m('message:error', $error);
		return false;
	}
	
	$orderData	= array();
	$form		= module('feedback:get:order');
	foreach($form as $name => $val)
	{
		$type = getFormFeedbackType($val);
		if (!$type) continue;
		if (@!$order[$name]) continue;
		$orderData[$type][$name] = $order[$name];
	}
	
	//	Подготовить данные для записи
	$d					= array();
	//	Формируем образ корзины, на момент формирования заказа
	$d['orderStatus']	= 'new';
	$d['user_id']		= userID();
	$d['orderBask']		= array();
	$d['totalPrice']	= 0;
	$d['orderData']		= $orderData;
	//	Формируем строку по которой будем искать в админке
	$d['searchField']	= makeOrderSearchField($orderData);
	//	bask
	$bask	= $order[':bask'];
	$ddb	= module('doc');
	
	//	Открываем товары
	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);

	$sql	= array();
	doc_sql($sql, $s);
	
	//	Формируем образ корзины
	$ddb->open($sql);
	if (!$ddb->rows()){
		m('message:error', "Нет товаров для заказа");
		return false;
	}
	while($data = $ddb->next())
	{
		$id	= $ddb->id();
		$data[':property']		= module("prop:getEx:$id");
		$data['orderCount']		= (int)$bask[$id];
		$data['orderPrice']		= docPrice($data);
		$d['orderBask'][$id]	= $data;

		$d['totalPrice']		+=$data['orderCount']*$data['orderPrice'];
	}
	//	Дата формирования заказа
	$d['orderDate']		= time();

	//	Запишем в базу
	$iid = $db->update($d);
	if (!$iid) return module('message:error', 'Ошибка записи в базу данных');

	@$fio	= implode(' ', $orderData['name']);
	logData("Order $iid \"$fio\" added", 'order');

	//	Для отправки писем сформируем событие
	$d = $db->openID($iid);
	event('order.changeStatus', $d);
	
	return $iid;
} ?>