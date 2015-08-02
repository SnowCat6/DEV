<? function order_ordered($db, $val, $data)
{
	$id		= $data[1];
	$key	= md5("order$id");
	if ($key != getValue('key')) return;
	
	$data		= $db->openID($id);
	@$orderData	= $data['orderData'];
	@$orderBask	= $data['orderBask'];
	$date		= $data['orderDate'];
	$date		= date('d.m.Y H:i', $date);
?>
{{page:title=Оформление закончено}}
<h2>Ваш номер заказа {$id}, дата и время заказа {$date}</h2>
{{read:orderBeforeCompleted}}
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
foreach($orderBask as $data)
{
	$ddb->setData($data);
	$iid		= $ddb->id();
	$price		= $data['orderPrice'];
	$totalPrice	= priceNumber($price*$data['orderCount']);
	$price		= priceNumber($price);
	$url		= getURL($ddb->url());
?>
  <tr>
    <td>{{doc:titleImage:$iid=size:50x50}}</td>
    <td>
		<a href="{!$url}">{$data[title]}</a>
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
<h3>Данные заказа</h3>
<table border="0" cellspacing="0" cellpadding="0" class="table" width="100%">
<? foreach($orderData as $type => $val){ ?>
<? foreach($val as $name => $value){?>
  <tr>
    <td valign="top" nowrap>{$name}</td>
    <td>{$value}</td>
  </tr>
<? } ?>
<? } ?>
</table>
{{read:orderAfterCompleted}}
<? }  ?>