<? function order_edit($db, $val, $data){
	if (!hasAccessRole('admin,developer,cashier')) return;

	$id			= $data[1];
	$data		= $db->openID($id);
	if (!$data) return;
	
	@$orderData	= unserialize($data['orderData']);
	$date		= makeDate($data['orderDate']);
	$date		= date('d.m.Y H:i', $date);
?>
<link rel="stylesheet" type="text/css" href="order.css">
{{page:title=Редактирование заказа}}
<h2>Заказ №{$id}, от {$date}</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <td nowrap class="orderStatus_{$data[orderStatus]}">Статус заказа</td>
    <td width="100%"><select name="order[orderStatus]" class="input w100">
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
    <td><input name="order[name]" value="{$orderData[name]}" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap>Телефон</td>
    <td><input name="order[phone]" value="{$orderData[phone]}" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap>Эл. почта</td>
    <td><input name="order[email]" value="{$orderData[email]}" type="text" class="input w100" /></td>
  </tr>
  <tr class="noBorder">
    <td colspan="2">Комментарий</td>
  </tr>
  <tr>
    <td colspan="2" class="orderNote"><textarea name="order[note]" cols="" rows="3" class="input w100">{$orderData[note]}</textarea></td>
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
$ddb = module('doc');
foreach($orderData['dbBask'] as $data){
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

<? } ?>