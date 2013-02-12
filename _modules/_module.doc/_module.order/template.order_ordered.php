<? function order_ordered($db, $val, $data){
	$id		= $data[1];
	$key	= md5("order$id");
	if ($key != getValue('key')) return;
?>
{{page:title=Оформление закончено}}
{{read:orderCompleted}}
<? }  ?>