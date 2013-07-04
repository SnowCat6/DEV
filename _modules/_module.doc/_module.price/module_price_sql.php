<?
function module_price_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	///////////////////////////////////////////
	//	Найти по цене
	if (@isset($search['price']))
	{
		$val		= $search['price'];
		$where		= '';
		$val		= explode('-', $val);
		@list($priceFrom, $priceTo) = $val;
		$priceFrom	= (float)trim($priceFrom);
		$priceTo	= (float)trim($priceTo);
		
		if ($priceFrom && $priceTo){
			$sql[] = "`price` BETWEEN $priceFrom AND $priceTo";
		}else
		if ($priceTo){
			$sql[] = "`price` <= priceTo";
		}else
		if (count($val) > 1) $sql[] = "price >= $priceFrom";
		else  $sql[] = "`price` = $priceFrom";
	}
}
?>