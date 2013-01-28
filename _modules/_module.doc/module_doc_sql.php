<?
function doc_sql(&$sql, &$search)
{
	$path = array();
	///////////////////////////////////////////
	//	Найти по типу документа
	if ($id = @$search['type']){
		$val	= htmlspecialchars($name);
		makeSQLValue($id);
		$sql[]	= "doc_type = $id";
	}

	@$date 	= makeDateStamp($search['date']);
	if ($date){
		@$from 	= makeDateStamp($search['date']);
		$to		= $from + 60*60*24;
	}else{
		@$from 	= makeDateStamp($search['dateFrom']);
		@$to 	= makeDateStamp($search['dateTo']);
	}
	if ($from || $to){
		$f = makeSQLDate($from);
		$t = makeSQLDate($to);
		
		if ($from && $to){
			$sql[] = "datePublish BETWEEN $f AND $t";
		}else
		if ($from){
			$sql[] = "datePublish >= $f";
		}else
		if ($to){
			$sql[] = "datePublish <= $t";
		}
	}
	if (@$val = (int)$search['month']){
		$sql[] = "MONTH(datePublish) = $val";
	}
	return $path;
}
?>