<?
//	+function bask_full_table
function bask_full_table($bask, $val, $data){?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>&nbsp;</th>
    <th width="100%" nowrap="nowrap">Название товара</th>
    <th nowrap="nowrap">Кол-во</th>
    <th nowrap="nowrap">Цена</th>
    <th nowrap="nowrap">Стоимость</th>
  </tr>
<?
$orderData	= $data['orderData'];
$orderBask	= $data['orderBask'];
$ddb = module('doc');
foreach($orderBask as $data)
{
	$ddb->setData($data);
	$iid		= $ddb->id();
	$url		= getURL($ddb->url());
	
	$price		= $data['orderPrice'];
	$totalPrice	= priceNumber($price*$data['orderCount']);
	$priceName	= $data['orderPriceName'];
	if (!$priceName) $priceName	= priceNumber($price) . ' руб.';
?>
  <tr>
    <td>{{doc:titleImage:$iid=size:50x50}}</td>
    <td>
		<a href="{!$url}">{$data[title]}</a>
		<div class="baskDetail">{!$data[itemDetail]}</div>
	</td>
    <td nowrap="nowrap">{$data[orderCount]} шт.</td>
    <td nowrap="nowrap" class="priceName">
<? if ($priceName){ ?>
	{!$priceName}
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
<? } ?>
