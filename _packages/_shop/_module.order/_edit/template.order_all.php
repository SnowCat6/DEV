<? function order_all($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,cashier')) return;

	module('script:ajaxLink');
	module('script:ajaxForm');

	$search	= getValue('search');
	if (!is_array($search)){
		$search = array();
		$search['status']['new'] = 'new';
	}
	
	if (is_array($orderDelete = getValue('orderDelete'))){
		$db->delete($orderDelete);
	}
	
	$sql	= array();
	if (@$val = $search['name']){
		$val	= $db->escape_string($val);
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
	if (isset($search['status'])){
		$status	= implode(',', $search['status']);
		$status	= makeIDS($status);
		$sql[]	= "`orderStatus` IN($status)";
	}

	module('script:calendar');
	module('script:jq');
?>
{{page:title=Оформленные заказы}}
<link rel="stylesheet" type="text/css" href="../../../_modules/_module.doc/_module.order/_edit/order.css">
<form action="{{getURL:order_all}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
  <th>№</th>
    <th>Дата</th>
    <th width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%" nowrap="nowrap">Ф.И.О. и комментарий</td>
        <td nowrap="nowrap"><label for="searchNew">Новые</label></td>
        <td><input type="checkbox" name="search[status][new]" id="searchNew" value="new"<?= isset($search['status']['new'])?' checked="checked"':''?> /></td>
        <td nowrap="nowrap"><label for="searchReceived">В обработке</label></td>
        <td><input type="checkbox" name="search[status][received]" id="searchReceived" value="received,delivery,wait"<?= isset($search['status']['received'])?' checked="checked"':''?> /></td>
        <td nowrap="nowrap"><label for="searchCompleted">Завершенные</label></td>
        <td><input type="checkbox" name="search[status][completed]" id="searchCompleted" value="complete,rejected"<?= isset($search['status']['completed'])?' checked="checked"':''?> /></td>
      </tr>
    </table></th>
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
	$("#searchNew, #searchReceived, #searchCompleted").change(function(){
		$(this).parents("form").submit();
	});
});
</script>
<? } ?>