<?
function order_update($db, $val, $order){
	if (!is_array($order)) return;
	
	@$fio	= trim($order['name']);
	if (!$fio) return module('message:error', 'Укажите ваше Ф.И.О');
	
	@$phone	= trim($order['phone']);
	@$mail	= trim($order['email']);
	
	if (!$phone && !$mail) return module('message:error', 'Укажите ваш телефон или E-mail');
	
	return 1;
} ?>