<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn($val, $data):NULL;
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
		compilePrice(&$data, false);
	}
}
function compilePrice(&$data, $bUpdate = true)
{
	
	if ($price = docPrice($data))
	{
		$docPrice	= getCacheValue('docPrice');
		foreach($docPrice as $maxPrice => $name){
			if ($price >= $maxPrice) continue;
			$data[':property']['Цена'] = $name;
			break;
		}
		if ($price >= $maxPrice){
			$data[':property']['Цена'] = $maxPrice;
		}
	}else{
			$data[':property']['Цена'] = '';
	}
	if ($bUpdate){
		$db	= module('doc', $data);
		$id	= $db->id();
		module("prop:set:$id", $data[':property']);
	}
}
function docPrice(&$data, $name = ''){
	if ($data['doc_type'] != 'product') return;
	if ($name == '') $name = 'base';
	switch($name){
	case 'old':		@$price	= $data['price_old'];	break;
	case 'base':	@$price	= $data['price'];		break;
	}
	return (float)$price;
}
function priceNumber($price){
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = ''){
	$price = docPrice(&$data, $name);
	if (!$price) return;
	
	$price = priceNumber($price);
	if ($name == 'old') return "<span class=\"price old\">$price</span>";
	return "<span class=\"price\">$price</span>";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat(&$data, $name);
	if ($price) return "<span class=\"priceName\">Цена: $price руб.</span>";
}

?>