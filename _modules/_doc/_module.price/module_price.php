<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn($val, $data):NULL;
}
function priceRate(){
	if (!defined('priceRate')){
		$ini	= getCacheValue('ini');
		$rate	= $ini[':priceRate'];
		$rate	= (float)$rate['rate'];
		if ($rate <= 0) $rate = 1;
		define('priceRate', $rate);
	}
	return priceRate;
}
function docPrice(&$data, $name = ''){
	if ($data['doc_type'] != 'product') return;
	if ($name == '') $name = 'base';
	switch($name){
	case 'old':		@$price	= $data['price_old'];	break;
	case 'base':	@$price	= $data['price'];		break;
	}
	return round((float)$price*priceRate());
}
function priceNumber($price){
	$price = str_replace(' ', '', $price);
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = '', $postfix='')
{
	$price = docPrice($data, $name);
	if ($price) $price = priceNumber($price);
	else{
		$postfix	= '';
		$price 		= '';
	}
	
	if ($name == 'old') return "<span class=\"price old\">$price</span>$postfix";
	return "<span class=\"price\">$price</span>$postfix";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat($data, $name);
	if ($price) return "<span class=\"priceName\">Цена: $price руб.</span>$postfix";
}
//	Вернуть статус заказа
function docPriceDelivery(&$data, $bFormat = true)
{
	$import		= isset($data['fields']['any']['import'][':raw'])?$data['fields']['any']['import'][':raw']:array();
	$delivery	= $import['delivery'];

	$lv	= '';	
	$d	= getCacheValue(':delivery');
	foreach($d as $name => $val){
		if ($delivery == $name) return $val;
		$lv	= $val;
	}
	return docPrice($data)?'':$v;
}
function price_update($val, &$evData)
{
	$d		= &$evData[0];
	$data	= &$evData[1];
	
	if (isset($data['price']))
	{
		$price = (float)$data['price'];
		$d['price']		= $price;
		$price = (float)$data['price_old'];
		$d['price_old']	= $price;
	}
}
?>
