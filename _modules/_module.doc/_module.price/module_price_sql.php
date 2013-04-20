<?
function price_sql(&$sql, $search)
{
	///////////////////////////////////////////
	//	Найти по цене
	if (@$val = $search['price'])
	{
		if ($val){
			$where		= '';
			$val		= explode('-', $val);
			@list($priceFrom, $priceTo) = $val;
			$priceFrom	= (float)trim($priceFrom);
			$priceTo	= (float)trim($priceTo);
			
			if ($priceFrom && $priceTo){
				$sql[] = "`price` BETWEEN $priceFrom AND $priceTo";
			}else
			if ($priceFrom){
				if (count($val) > 1) $sql[] = "price >= $priceFrom";
				else  $sql[] = "`price` = $priceFrom";
			}else
			if ($priceTo){
				$sql[] = "`price` <= priceTo";
			}
		}
	}
}
?>