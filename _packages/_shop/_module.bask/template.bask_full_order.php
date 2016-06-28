<?
//	+function bask_full_order
function bask_full_order($bask, $val, $data)
{
	$orderData	= $data['orderData'];
	$orderBask	= $data['orderBask'];
?>
<div class="pageContent">
<h1>Данные заказа</h1>
<table border="0" cellspacing="0" cellpadding="0" class="table" width="100%">
<? foreach($orderData as $type => $val){ ?>
<? foreach($val as $name => $value){?>
  <tr>
    <td valign="top" nowrap>{$name}</td>
    <td width="100%">{$value}</td>
  </tr>
<? } ?>
<? } ?>
</table>
</div>
<br><br>
<? } ?>
