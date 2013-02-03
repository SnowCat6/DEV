<?
function doc_sql(&$sql, &$search)
{
	$path = array();

	///////////////////////////////////////////
	//	Найти по номеру документа
	if ($val = @$search['id'])
	{
		$val	= makeIDS($val);
		$sql[]	= "doc_id IN($val)";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "doc_type IN($val)";
	}
	
	prop_sql(&$sql, &$search);

	return $path;
}
?>