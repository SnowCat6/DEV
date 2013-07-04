<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn(&$val, &$data):NULL;
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
	$price = str_replace(' ', '', $price);
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
function price_query($val, &$evData)
{
	foreach(explode("\r\n", $evData[0]) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL(trim($q));
		if ($name && $q) $evData[1][$name]	= $q;
	};
}
function makePropertySQL($q)
{
	list($q1, $q2) = explode('-', $q);
	$q1 = (int)$q1;
	$q2 = (int)$q2;
	
	if ($q1 && $q2){
		return "`price` BETWEEN $q1 AND $q2";
	}else
	if ($q1){
		return "`price` > $q1";
	}else
	if ($q2){
		return "`price` < $q2";
	}
}

?>