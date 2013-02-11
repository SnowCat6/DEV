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
				$where = "price BETWEEN $priceFrom AND $priceTo";
			}else
			if ($priceFrom){
				if (count($val) > 1) $where = "price >= $priceFrom";
				else  $where = "price = $priceFrom";
			}else
			if ($priceTo){
				$where = "price <= priceTo";
			}

			if ($where){
				$db		= module('price');
				$table	= $db->table();
				$sql[]	= "`doc_id` IN (SELECT `doc_id` FROM $table WHERE $where)";
			}
		}
	}
}
?>