<? function order_edit($db, $val, $data){
	if (!hasAccessRole('admin,developer,cashier')) return;

	$id			= $data[1];
	$data		= $db->openID($id);
	if (!$data) return;
	@$data['orderData'] = unserialize($data['orderData']);
	if (!is_array($data['orderData'])) $data['orderData'] = array();
	
	$order = getValue('order');
	if (is_array($order)){
		dataMerge($order, $data);
		$order['id'] = $id;
		$db->update($order);

		//	Для отправки писем сформируем событие
		if ($order['orderStatus'] != $data['orderStatus'])
			event('order.changeStatus', $order);

		return module('order:all');
	}
	
	module('script:ajaxForm');
	
	$orderData	= $data['orderData'];
	$date		= makeDate($data['orderDate']);
	$date		= date('d.m.Y H:i', $date);
?>
<link rel="stylesheet" type="text/css" href="order.css">
{{page:title=Редактирование заказа}}
<form action="{{getURL:order_edit$id}}" method="post" class="form ajaxFrom ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td><h2>Заказ №{$id}, от {$date}</h2></td>
    <td align="right"><input type="submit" class="button" value="Записать" /></td>
</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <td nowrap class="orderStatus_{$data[orderStatus]}">Статус заказа</td>
    <td width="100%" nowrap="nowrap"><select name="order[orderStatus]" class="input w100">
<?
$orderTypes = getCacheValue('orderTypes');
foreach($orderTypes as $type => $name){
	$class	= "orderStatus_$type";
	$class	= $type==$data['orderStatus']?" selected=\"selected\" class=\"$class\"":" class=\"$class\"";
?>
<option value="{$type}"{!$class}>{$name}</option>
<? } ?>
    </select></td>
  </tr>
  <tr>
    <td nowrap>Ф.И.О.</td>
    <td><input name="order[orderData][name]" value="{$orderData[name]}" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap>Телефон</td>
    <td><input name="order[orderData][phone]" value="{$orderData[phone]}" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap>Эл. почта</td>
    <td><input name="order[orderData][email]" value="{$orderData[email]}" type="text" class="input w100" /></td>
  </tr>
  <tr class="noBorder">
    <td colspan="2">Комментарий</td>
  </tr>
  <tr>
    <td colspan="2" class="orderNote"><textarea name="order[orderData][note]" cols="" rows="3" class="input w100">{$orderData[note]}</textarea></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>&nbsp;</th>
    <th width="100%" nowrap="nowrap">Название товара</th>
    <th nowrap="nowrap">Кол-во</th>
    <th nowrap="nowrap">Цена</th>
    <th nowrap="nowrap">Стоимость</th>
  </tr>
<?
$ddb	= module('doc');
$bask	= $orderData['dbBask'];
if (!is_array($bask)) $bask = array();
foreach($bask as $data){
	$ddb->data	= $data;
	$iid		= $ddb->id();
	$price		= $data['orderPrice'];
	$totalPrice	= priceNumber($price*$data['orderCount']);
	$price		= priceNumber($price);
	$url		= getURL($ddb->url());
	$folder		= docTitle($iid);
?>
  <tr>
    <td><? displayThumbImage($folder, array(50, 50), '', '', $folder)?></td>
    <td><a href="{!$url}">{$data[title]}</a></td>
    <td nowrap="nowrap">{$data[orderCount]} шт.</td>
    <td nowrap="nowrap" class="priceName">{$price} руб.</td>
    <td nowrap="nowrap" class="priceName">{$totalPrice} руб.</td>
  </tr>
<? } ?>
</table>
</form>
<? } ?>