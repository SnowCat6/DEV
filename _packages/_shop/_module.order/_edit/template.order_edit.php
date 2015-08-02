<? function order_edit($db, $val, $data){
	if (!hasAccessRole('admin,developer,cashier')) return;

	$id			= $data[1];
	$data		= $db->openID($id);
	if (!$data) return;
	$data['orderData'] = $data['orderData'];
	if (!is_array($data['orderData'])) $data['orderData'] = array();
	
	$order = getValue('order');
	if (is_array($order)){
		dataMerge($order, $data);
		$order['id']			= $id;
		$order['searchField']	= makeOrderSearchField($order['orderData']);
		$db->update($order);

		$order = $db->openID($id);
		//	Для отправки писем сформируем событие
		if ($order['orderStatus'] != $data['orderStatus']){
			event('order.changeStatus', $order);
		}

		$fio	= implode(' ', $data['orderData']['name']);
		logData("Order $id \"$fio\" updated", 'order');

		return module('order:all');
	}
	
	m('script:jq_ui');
	m('script:ajaxForm');
	m('script:preview');
	
	$orderData	= $data['orderData'];
	$date		= $data['orderDate'];
	$date		= date('d.m.Y H:i', $date);
?>
<link rel="stylesheet" type="text/css" href="../../../_modules/_module.doc/_module.order/_edit/order.css">
{{ajax:template=ajax_edit}}
{{page:title=Редактирование заказа №$id  от $date}}
<form action="{{getURL:order_edit$id}}" method="post" class="ajaxFrom ajaxReload">

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#order1">Заказ №{$id}  от {$date}</a></li>
    <li class="ui-corner-top"><a href="#order2">Список товаров</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="order1">
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
    <td valign="top" nowrap>Комментарий менеджера</td>
    <td><textarea name="order[orderNote]" cols="" rows="4" class="input w100">{$data[orderNote]}</textarea></td>
  </tr>
<? foreach($orderData as $type => $val){ ?>
<? foreach($val as $name => $value){?>
  <tr>
    <td valign="top" nowrap>{$name}</td>
    <td>
<? if ($type != 'textarea'){ ?>
    <input name="order[orderData][{$type}][{$name}]" value="{$value}" type="text" class="input w100" />
<? }else{ ?>
    <textarea name="order[orderData][{$type}][{$name}]" rows="3" class="input w100">{$value}</textarea>
<? } ?>
    </td>
  </tr>
<? } ?>
<? } ?>
</table>
</div>

<div id="order2">
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
@$bask	= $data['orderBask'];
if (!is_array($bask)) $bask = array();
foreach($bask as $data){
	$ddb->data	= $data;
	$iid		= $ddb->id();
	$price		= $data['orderPrice'];
	$totalPrice	= priceNumber($price*$data['orderCount']);
	$price		= priceNumber($price);
	$url		= getURL($ddb->url());
	$folder		= docTitleImage($iid);
?>
  <tr>
    <td>{{doc:titleImage:$id=size:50x50}}</td>
    <td>
		<a href="{!$url}" class="preview">{$data[title]}</a>
		<div class="baskDetail">{!$data[itemDetail]}</div>
	</td>
    <td nowrap="nowrap">{$data[orderCount]} шт.</td>
    <td nowrap="nowrap" class="priceName">
<? if ($price){ ?>
	{$price} руб.
<? } ?>
	</td>
    <td nowrap="nowrap" class="priceName">
<? if ($price){ ?>
	{$totalPrice} руб.
<? } ?>
	</td>
  </tr>
<? } ?>
</table>
</div>

</div>

</form>
{{script:adminTabs}}
<? } ?>