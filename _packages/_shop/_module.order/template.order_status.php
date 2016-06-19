<?
function order_status($db, $val, $order)
{
	//	Глобальные настройки
	$ini		= getCacheValue('ini');

	$status			= $order['orderStatus'];
	if (!is_file($mailTemplate = images."/mailTemplates/order_$status.txt")) $mailTemplate = '';
	if (!$mailTemplate && !is_file($mailTemplate = cacheRootPath."/mailTemplates/order_$status.txt")) $mailTemplate = '';

	$mailTo = '';
	if (!$mailTo) @$mailTo = $ini[':mail']['mailOrder'];
	if (!$mailTo) @$mailTo = $ini[':mail']['mailAdmin'];

	switch($status){
	case 'new':			$title = "Получен новый заказ $order[order_id]";
		break;
	case 'received':	$title = "Заказз $order[order_id] в обработке";
		break;
	case 'wait':		$title = "Заказз $order[order_id] ожидает доставки";
		break;
	case 'delivery':	$title = "Заказз $order[order_id] доставляется";
		break;
	case 'completed':	$title = "Заказз $order[order_id] доставлен";
		break;
	case 'rejected':	$title = "Заказз $order[order_id] отменен";
		break;
	default:
						$title	= "Неизвестный статус заказа $order[order_id] '$status'";
	}

	$mail	= makeOrderMail($db, $order);
	module("mail:send:$mailFrom:$mailTo:$mailTemplate:$title", $mail);
}
function makeOrderMail($db, $order)
{
	@$orderData = $order['orderData'];
	
	$mail = array();
	$mail['order_id']	= $order['order_id'];
	$mail['orderDate']	= date('d.m.Y H:i', $order['orderDate']);
	$mail['orderURL']	= getURLEx("order_edit$mail[order_id]");
	
	$mail['name']		= implode(' ', $orderData['name']);
	$mail['phone']		= implode(' ', $orderData['phone']);
	$mail['mailFrom']	= implode(' ', $orderData['email']);
	$mail['note']		= implode(' ', $orderData['textarea']);
	$mail['totalPrice']	= $order['totalPrice'];
	
	$plain	= '';
	$html	= '';
	$SMS	= '';
	$dbBask	= $order['orderBask'];
	foreach($dbBask as $iid => $data)
	{
		$detailHTML	= $data['itemDetail'];
		if ($detailHTML) $detailHTML = " ($detailHTML)";
		$detailPlain= strip_tags($detailHTML);
		
		$price		= $data['orderPrice'];
		$priceName	= $data['orderPriceName'];
		if (!$priceName) $priceName	= priceNumber($price) . ' руб.';
		
		$plain	.= "$data[title]$detailPlain, $data[orderCount] шт., $priceName/шт.\r\n";
		$SMS	.= "$data[title]$detailPlain, $data[orderCount] шт.\r\n";
		$html	.= "<div><b>$data[title]</b>$detailHTML, $data[orderCount] шт., <b>$priceName/шт.</b></div>";
	}
	$plain	.= "-----------------------------\r\n";
	$plain	.= "Итого: $mail[totalPrice] руб.\r\n";
	$SMS	.= "-----\r\n";
	$SMS	.= "Итого: $mail[totalPrice] руб.\r\n";
	$html	.= "<hr />";
	$html	.= "<div>Итого: <b>$mail[totalPrice] руб.</b></div>";
	
	$mail['plain']	= $plain;
	$mail['html']	= $html;
	$mail['SMS']	= $SMS;

	$a	= array(
		'title'	=> &$title,
		'order'	=> &$order,
		'mail'	=> &$mail
	);
	event('order.mail', $a);
	
	return $mail;
}
?>