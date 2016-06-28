<? function order_edit($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,cashier')) return;

	$id			= $data[1];
	$data		= $db->openID($id);
	if (!$data) return;
	$data['orderData'] = $data['orderData'];
	if (!is_array($data['orderData'])) $data['orderData'] = array();
	
	$order = getValue('order');
	if (is_array($order))
	{
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
		undo::addLog("Order $id \"$fio\" updated", 'order');

		return module('order:all');
	}
	
	m('script:jq_ui');
	m('script:ajaxForm');
	m('script:preview');
	
	$orderData	= $data['orderData'];
	$date		= $data['orderDate'];
	$date		= date('d.m.Y H:i', $date);
?>
<link rel="stylesheet" type="text/css" href="../../_module.bask/css/bask.css">
<link rel="stylesheet" type="text/css" href="../css/order.css">

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
<?
/*
foreach($orderData as $type => $val){
foreach($val as $name => $value){ 
*/
foreach(module('feedback:get:order') as $name=>$d)
{
	$type	= getFormFeedbackType($d);
	if (!$type || $name[0]==':') continue;
?>
  <tr>
    <td valign="top" nowrap>{$name}</td>
    <td>
<? if ($type != 'textarea'){ ?>
    <input type="text" class="input w100" name="order[orderData][{$type}][{$name}]" value="{$orderData[$type][$name]}" />
<? }else{ ?>
    <textarea class="input w100" rows="10" name="order[orderData][{$type}][{$name}]">{$orderData[$type][$name]}</textarea>
<? } ?>
    </td>
  </tr>
<? } ?>
</table>
</div>

<div id="order2">
	<module:bask:full_table @="$data" />
</div>

</div>

</form>
{{script:adminTabs}}
<? } ?>