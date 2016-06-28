<? function order_ordered($db, $val, $data)
{
	$id		= $data[1];
	$key	= md5("order$id");
	if ($key != getValue('key')) return;
	
	$data		= $db->openID($id);
?>
    {{page:title=Оформление закончено}}
<div class="pageContent">
    <h2>Ваш номер заказа {$id}, дата и время заказа {{date:<b>%d.%m.%Y</b> <small>%H:%i</small>=$data[orderDate]}}</h2>
    {{read:orderBeforeCompleted}}
</div>
	<module:bask:full_order @="$data" />
	<module:bask:full_table @="$data" />
<div class="pageContent">
    {{read:orderAfterCompleted}}
</div>
<? } ?>
