<? function order_all($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,cashier')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');

	$search	= getValue('search');
	if (!is_array($search)) $search = array();
	
	if (is_array($orderDelete = getValue('orderDelete'))){
		$db->delete($orderDelete);
	}
	
	$sql	= array();
	if (@$val = $search['name']){
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`searchField` LIKE ('%$val%')";
	}
	if (@$val = $search['id']){
		$val	= makeIDS($val);
		$sql[]	= "order_id IN ($val)";
	}
	if (@$val = $search['date']){
		$val	= makeSQLDate(makeDateStamp($val));
		$sql[]	= "orderDate <= $val";
	}

	module('script:calendar');
?>
{{page:title=Оформленные заказы}}
<link rel="stylesheet" type="text/css" href="../../../_modules/_module.doc/_module.order/_edit/order.css">
<form action="{{getURL:order_all}}" method="post" class="admin form ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
  <th>№</th>
    <th>Дата</th>
    <th width="100%">Ф.И.О. и комментарий</th>
    <th>Суммма</th>
</tr>
<tr class="search">
  <td><input name="search[id]" type="text" class="input w100" value="{$search[id]}" size="4" /></td>
  <td><input type="text" name="search[date]" class="input w100" id="calendar" value="{$search[date]}" /></td>
  <td><input type="text" name="search[name]" class="input w100" value="{$search[name]}" /></td>
  <td><input type="submit" name="Submit" class="button w100" value="Искать" /></td>
</tr>
<?
$db->order = 'orderDate DESC';
$db->open($sql);
while($data = $db->next())
{
	$id			= $db->id();
	@$orderData	= unserialize($data['orderData']);
	$date		= makeDate($data['orderDate']);
	if (date('Y') == date('Y', $date)){
		if (date('z') == date('z', $date)){
			$date = date('<b>H:i</b>', $date);
		}else{
			$date = date('d.m.Y', $date);
		}
	}else{
		$date = date('d.m.Y', $date);
	}
	@$price	= priceNumber($data['totalPrice']);
	@$name	= implode(' ', $orderData['name']);
	@$note	= $orderData['textarea'];
	if (!is_array($note)) $note = array();
	$class	= $note?'class="noBorder"':'';
?>
<tr {!$class}>
    <td nowrap><input name="orderDelete[]" type="checkbox" value="{$id}" /></td>
    <td nowrap class="orderStatus_{$data[orderStatus]}">{!$date}</td>
    <td><a href="{{getURL:order_edit$id}}" id="ajax">{$name}</a></td>
    <td nowrap>{$price} руб.</td>
</tr>
<? if ($note){ ?>
<tr>
    <td colspan="4" class="orderNote"><? foreach($note as $name => $val){?><div><b>{$name}:</b> {$val}</div><? } ?></td>
</tr>
<? } ?>
<? } ?>
</table>
</form>
<? } ?>