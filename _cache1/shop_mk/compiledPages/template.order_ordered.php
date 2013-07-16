<? function order_ordered($db, $val, $data){
	$id		= $data[1];
	$key	= md5("order$id");
	if ($key != getValue('key')) return;
	
	$data		= $db->openID($id);
	@$orderData	= unserialize($data['orderData']);
	@$orderBask	= unserialize($data['orderBask']);
	$date		= makeDate($data['orderDate']);
	$date		= date('d.m.Y H:i', $date);
?>
<? $module_data = array(); $module_data[] = "Оформление закончено"; moduleEx("page:title", $module_data); ?>
<h2>Ваш номер заказа <? if(isset($id)) echo htmlspecialchars($id) ?>, дата и время заказа <? if(isset($date)) echo htmlspecialchars($date) ?></h2>
<? module("read:orderBeforeCompleted"); ?>
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
foreach($orderBask as &$data){
	$ddb->data	= $data;
	$iid		= $ddb->id();
	$price		= $data['orderPrice'];
	$totalPrice	= priceNumber($price*$data['orderCount']);
	$price		= priceNumber($price);
	$url		= getURL($ddb->url());
	$folder		= docTitleImage($iid);
?>
  <tr>
    <td><? displayThumbImage($folder, array(50, 50), '', '', $folder)?></td>
    <td><a href="/<? if(isset($url)) echo $url ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></td>
    <td nowrap="nowrap"><? if(isset($data["orderCount"])) echo htmlspecialchars($data["orderCount"]) ?> шт.</td>
    <td nowrap="nowrap" class="priceName"><? if(isset($price)) echo htmlspecialchars($price) ?> руб.</td>
    <td nowrap="nowrap" class="priceName"><? if(isset($totalPrice)) echo htmlspecialchars($totalPrice) ?> руб.</td>
  </tr>
<? } ?>
</table>
<h3>Данные заказа</h3>
<table border="0" cellspacing="0" cellpadding="0" class="table" width="100%">
<? foreach($orderData as $type => $val){ ?>
<? foreach($val as $name => $value){?>
  <tr>
    <td valign="top" nowrap><? if(isset($name)) echo htmlspecialchars($name) ?></td>
    <td><? if(isset($value)) echo htmlspecialchars($value) ?></td>
  </tr>
<? } ?>
<? } ?>
</table>
<? module("read:orderAfterCompleted"); ?>
<? }  ?>