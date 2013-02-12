<? function order_all($db, $val, $data){
	if (!hasAccessRole('admin,developer,cashier')) return;
	module('script:ajaxLink');
?>
{{page:title=Оформленные заказы}}
<link rel="stylesheet" type="text/css" href="order.css">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>Дата</th>
    <th width="100%">Ф.И.О. и комментарий</th>
    <th>Суммма</th>
</tr>
<?
$db->order = 'orderDate DESC';
$db->open();
while($data = $db->next()){
	$id			= $db->id();
	@$orderData	= unserialize($data['orderData']);
	$date		= makeDate($data['orderDate']);
	if (date('Y') == date('Y', $date)){
		if (date('z') == date('z', $date)){
			$date = date('<b>H:i</b>', $date);
		}else{
			$date = date('d.m <b>H:i</b>', $date);
		}
	}else{
		$date = date('d.m.Y', $date);
	}
	@$price	= priceNumber($orderData['totalPrice']);
	@$note	= $orderData['note'];
	$class	= $note?'class="noBorder"':'';
?>
<tr {!$class}>
    <td nowrap class="orderStatus_{$data[orderStatus]}">{!$date}</td>
    <td><a href="{{getURL:order_edit$id}}" id="ajax">{$orderData[name]}</a></td>
    <td nowrap>{$price} руб.</td>
</tr>
<? if ($note){ ?>
<tr>
    <td colspan="3" class="orderNote">{$note}</td>
</tr>
<? } ?>
<? } ?>
</table>

<? } ?>