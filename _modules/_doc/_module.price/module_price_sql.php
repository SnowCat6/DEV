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

//	Сформировать запрос для диапазона, rate используется для коррекции цифрового значения в записимости от курса
function makePropertySQL($field, $q, $rate = 1)
{
	list($q1, $q2) = explode('-', $q);
	$q1 = (int)$q1 / $rate;
	$q2 = (int)$q2 / $rate;
	
	if ($q1 && $q2){
		return "($field >= $q1 AND $field < $q2)";
	}else
	if ($q1){
		return "$field >= $q1";
	}else
	if ($q2){
		return "$field <= $q2";
	}
}
?>
