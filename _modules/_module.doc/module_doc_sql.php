<?
function doc2sql($search){
	$sql = array();
	doc_sql(&$sql, &$search);
	return $sql;
}
function doc_sql(&$sql, $search)
{
	$path = array();
	
	///////////////////////////////////////////
	//	Найти по номеру документа
	if (isset($search['id']))
	{
		$val	= $search['id'];
		$val	= makeIDS($val);
		if ($val) $sql[]	= "`doc_id` IN ($val)";
		else $sql[] = 'true = false';
	}

	if (@$val = $search['title'])
	{
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`title` LIKE ('%$val%')";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`doc_type` IN ($val)";
	}
	
	prop_sql(&$sql,	&$search);
	price_sql(&$sql,&$search);

	return $path;
}
?>