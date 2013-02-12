<? function order_all($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,cashier')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');

	$search	= getValue('search');
	if (!is_array($search)) $search = array();
	
	$sql	= array();
	if (@$val = $search['name']){
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`searchField` LIKE ('%$val%')";
	}
	if (@$val = $search['id']){
		$val	= makeIDS($val);
		$sql[]	= "order_id IN ($val)";
	}
?>
{{page:title=Оформленные заказы}}
<link rel="stylesheet" type="text/css" href="order.css">
<form action="{{getURL:order_all}}" method="post" class="form ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>Дата</th>
    <th width="100%">Ф.И.О. и комментарий</th>
    <th>Суммма</th>
</tr>
<tr class="search">
  <td><input type="text" name="search[id]" class="input w100" value="{$search[id]}" /></td>
  <td><input type="text" name="search[name]" class="input w100" value="{$search[name]}" /></td>
  <td><input type="submit" name="Submit" class="button w100" value="Искать" /></td>
</tr>
<?

$db->order = 'orderDate DESC';
$db->open($sql);
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
</form>
<? } ?>