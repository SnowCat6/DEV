<? function order_ordered($db, $val, $data){
	$id		= $data[1];
	$key	= md5("order$id");
	if ($key != getValue('key')) return;
	
	$data		= $db->openID($id);
	@$orderData	= unserialize($data['orderData']);
	$date		= makeDate($data['orderDate']);
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
<h3>Данные заказа</h3>
<table border="0" cellspacing="0" cellpadding="0" class="table" width="100%">
<tr>
    <td nowrap="nowrap">Ф.И.О.</td>
    <td width="100%">{$orderData[name]}</td>
</tr>
<? if (@$orderData['phone']){ ?>
<tr>
    <td nowrap="nowrap">Контактный телефон</td>
    <td>{$orderData[phone]}</td>
</tr>
<? } ?>
<? if (@$orderData['mail']){ ?>
<tr>
    <td nowrap="nowrap">Контактный e-mail</td>
    <td>{$orderData[phone]}</td>
</tr>
<? } ?>
</table>
<? if (@$orderData['note']){ ?>
<h3>Ваш комментарий:</h3>
<blockquote>{$orderData[note]}</blockquote>
<? } ?>
{{read:orderAfterCompleted}}
<? }  ?>