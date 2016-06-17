<? function order_all($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,cashier')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');

	if (is_array($orderDelete = getValue('orderDelete'))){
		$db->delete($orderDelete);
	}
	
	$search	= getValue('search');
	if (!is_array($search))	$search = array();
	if (!$search['status']) $search['status'] = 'new';
	
	$s		= $search;
	switch($search['status']){
		case 'received':
		$s['status']	= 'received,delivery,wait';
	}
	
	$sql	= array();
	if (@$val = $s['name']){
		$val	= $db->escape_string($val);
		$sql[]	= "`searchField` LIKE ('%$val%')";
	}
	if (@$val = $s['id']){
		$val	= makeIDS($val);
		$sql[]	= "order_id IN ($val)";
	}
	if (@$val = $s['date']){
		$val	= makeDateStamp($val);
		$val	= dbEncDate($db, $val);
		$sql[]	= "orderDate <= $val";
	}
	if (isset($s['status'])){
		$status	= $s['status'];
		$status	= makeIDS($status);
		$sql[]	= "`orderStatus` IN($status)";
	}

	module('script:calendar');
	module('script:jq');
?>
{{page:title=Оформленные заказы}}
<link rel="stylesheet" type="text/css" href="../css/order.css">
<form action="{{getURL:order_all}}" method="post" class="admin">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="radioFilter">
  <tr>
    <td width="100%">&nbsp;</td>
    
    <td nowrap="nowrap"><label>
    	<input type="radio" name="search[status]" value="new" {checked:$search[status]=='new'} /> Новые
     </label></td>

    <td nowrap="nowrap"><label>
        <input type="radio" name="search[status]" value="received" {checked:$search[status]=='received'} /> В обработке
     </label></td>
    
    <td nowrap="nowrap"><label>
        <input type="radio" name="search[status]" value="complete" {checked:$search[status]=='complete'} /> Завершенные
     </label></td>
    
    <td nowrap="nowrap"><label>
        <input type="radio" name="search[status]" value="rejected" {checked:$search[status]=='rejected'} />Удаленные
     </label></td>
  </tr>
</table>

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
	@$orderData	= $data['orderData'];
	$date		= $data['orderDate'];
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
	@$name	= trim(implode(' ', $orderData['name']));
	if (!$name) $name = 'no name';
	@$note	= $orderData['textarea'];
	if (!is_array($note)) $note = array();
	$note2	= $data['orderNote'];
	$class	= $note || $note2?'class="noBorder"':'';
?>
<tr {!$class}>
    <td nowrap><input name="orderDelete[]" type="checkbox" value="{$id}" /></td>
    <td nowrap class="orderStatus_{$data[orderStatus]}">{!$date}</td>
    <td><a href="{{getURL:order_edit$id}}" id="ajax">{$name}</a></td>
    <td nowrap>{$price} руб.</td>
</tr>
<? if ($note2){ ?>
<tr>
    <td colspan="4" class="orderNote manager">{$note2}</td>
</tr>
<? } ?>
<? if ($note){ ?>
<tr>
    <td colspan="4" class="orderNote"><? foreach($note as $name => $val){?><div><b>{$name}:</b> {$val}</div><? } ?></td>
</tr>
<? } ?>
<? } ?>
</table>
</form>
<script>
$(function(){
	$(".radioFilter input").change(function(){
		$(this).parents("form").submit();
	});
});
</script>
<? } ?>