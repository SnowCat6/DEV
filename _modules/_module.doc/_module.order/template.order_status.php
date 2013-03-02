<?
function order_status($db, $val, $order)
{
	//	Глобальные настройки
	$ini		= getCacheValue('ini');

	$status			= $order['orderStatus'];
	if (!is_file($mailTemplate = images."/mailTemplates/order_$status.txt")) $mailTemplate = '';
	if (!$mailTemplate && !is_file($mailTemplate = localCacheFolder."/siteFiles/mailTemplates/order_$status.txt")) $mailTemplate = '';

	$mailTo = '';
	if (!$mailTo) @$mailTo = $ini[':mail']['mailOrder'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];

	$title			= "Неизвестный статус заказа '$status'";
	switch($status){
	case 'new':			$title = 'Получен новый заказ';
	break;
	case 'received':	$title = 'Заказ в обработке';
	break;
	case 'wait':		$title = 'Заказ ожидает доставки';
	break;
	case 'delivery':	$title = 'Заказ доставляется';
	break;
	case 'completed':	$title = 'Заказ доставлен';
	break;
	case 'rejected':	$title = 'Заказ отменен';
	break;
	}

	$mail	= makeOrderMail($db, $order);
	module("mail:send:$title:$mailFrom:$mailTo:$mailTemplate", $mail);
}
function makeOrderMail($db, $order)
{
	@$orderData = unserialize($order['orderData']);
	
	$mail = array();
	$mail['order_id']	= $order['order_id'];
	$mail['orderDate']	= date('d.m.Y H:i', makeDate($order['orderDate']));
	$mail['orderURL']	= getURLEx("order_edit$mail[order_id]");
	
	$mail['name']		= $orderData['name'];
	$mail['phone']		= $orderData['phone'];
	$mail['mailFrom']	= $orderData['email'];
	$mail['note']		= $orderData['note'];
	$mail['totalPrice']	= $orderData['totalPrice'];
	
	$plain	= '';
	$html	= '';
	$dbBask	= $orderData['dbBask'];
	foreach($dbBask as $iid => $data){
		$plain	.= "$data[title], $data[orderCount] шт., $data[orderPrice] руб./шт.\r\n";
		$html	.= "<div><b>$data[title]</b>, $data[orderCount] шт., <b>$data[orderPrice] руб./шт.</b></div>";
	}
	$plain	.= "-----------------------------\r\n";
	$plain	.= "Итого: $mail[totalPrice] руб.\r\n";
	$html	.= "<hr />";
	$html	.= "<div>Итого: <b>$mail[totalPrice] руб.</b></div>";
	
	$mail['plain']	= $plain;
	$mail['html']	= $html;
	
	return $mail;
}
?>