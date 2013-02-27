<?
function order_add($db, $val, $order)
{
	if (!is_array($order)) return;
	
	//	Проверить корректоность имени
	@$fio	= trim($order['name']);
	if (!$fio) return module('message:error', 'Укажите ваше Ф.И.О');
	
	//	Проверить наличие обратной связи
	@$phone	= trim($order['phone']);
	@$mail	= trim($order['email']);
	
	if (!$phone && !$mail) return module('message:error', 'Укажите ваш телефон или E-mail');
	
	//	Подготовить данные для записи
	$d				= array();
	//	Формируем образ корзины, на момент формирования заказа
	$d['orderStatus']	= 'new';
	//	bask
	$bask	= $order['bask'];
	$ddb	= module('doc');
	$order['dbBask']	= array();
	$order['totalPrice']= 0;
	
	//	Открываем товары
	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);

	$sql	= array();
	doc_sql(&$sql, $s);
	
	//	Формируем образ корзины
	$ddb->open($sql);
	while($data = $ddb->next())
	{
		$id	= $ddb->id();
		$data['orderCount']		= (int)$bask[$id];
		$data['orderPrice']		= docPrice($data);
		$order['dbBask'][$id]	= $data;

		$order['totalPrice']	+=$data['orderCount']*$data['orderPrice'];
	}
	//	Запомнинаем образ
	$d['orderData']	= $order;
	//	Формируем строку по которой будем искать в админке
	$d['searchField']	= orderSearchField($order);
	//	Дата формирования заказа
	$d['orderDate']	= makeSQLDate(mktime());

	//	Запишем в базу
	$iid = $db->update($d);
	if (!$iid) return module('message:error', 'Ошибка записи в базу данных');

	//	Для отправки писем сформируем событие
	$d = $db->openID($iid);
	event('order.changeStatus', $d);
	
	return $iid;
} ?>