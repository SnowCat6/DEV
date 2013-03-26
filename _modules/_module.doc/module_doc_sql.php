<?
function doc2sql($search){
	$sql = array();
	doc_sql(&$sql, &$search);
	return $sql;
}
function doc_sql(&$sql, $search)
{
	$path	= array();

	///////////////////////////////////////////
	//	Найти по номеру документа
	if (isset($search['id']))
	{
		$val	= $search['id'];
		$val	= makeIDS($val);
		if ($val) $sql[]	= "`doc_id` IN ($val)";
		else $sql[] = 'false';
	}

	if (@$val = $search['title'])
	{
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`title` LIKE ('%$val%')";
	}

	if (@$val = $search['template'])
	{
		makeSQLValue($val);
		$sql[]	= "`template` = $val";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`doc_type` IN ($val)";
	}
	
	//	Если ищется по имени
	if ($val = @$search['name']){
		$s = array();

		//	Или название / рус, енг
		$v = docPrepareSearch($val);
		$v = trim($v);
		if ($v){
			$e		= array();	//	Exclude words
//			if (is_int(strpos('вентилятор', $v))) $e[] = 'обогреватель';
			
			$name 	= htmlspecialchars(docPrepareSearch($val, false));
			$path[] = "название <b>$name</b>";
			$v 		= str_replace(' ', '* +', $v);
			
			if ($e)	$e = ' -'.implode(' -', $e);
			else $e = '';
			
			$s[]	= "MATCH (`searchTitle`) AGAINST ('+$v*$e' IN BOOLEAN MODE)";
		}
		if ($s)	$sql[] = '('.implode(' OR ', $s).')';
	}
	//	Если ищется по имени
	if ($val = @$search['document']){
		$s = array();

		//	Или название / рус, енг
		$v = docPrepareSearch($val);
		$v = trim($v);
		if ($v){
			$e		= array();	//	Exclude words
//			if (is_int(strpos('вентилятор', $v))) $e[] = 'обогреватель';
			
			$name 	= htmlspecialchars(docPrepareSearch($val, false));
			$path[] = "слова <b>$name</b>";
			$v 		= str_replace(' ', '* +', $v);
			
			if ($e)	$e = ' -'.implode(' -', $e);
			else $e = '';
			
			$s[]	= "MATCH (`searchTitle`, `searchDocument`) AGAINST ('+$v*$e' IN BOOLEAN MODE)";
		}
		if ($s)	$sql[] = '('.implode(' OR ', $s).')';
	}

	prop_sql(&$sql,	&$search);
	price_sql(&$sql,&$search);
	
	if (@$sql[':from'] || @$sql[':join']){
		$sql[':from'][] = 'd';
	}
	
	return $path;
}
//	Убрать все неиндексируемые символы, одиночные буквы и цифры расщирить до 4х знаков
function docPrepareSearch($val, $bFullPrepare = true){
	$val = preg_replace('#[^0-9a-zа-я]#iu', ' ', $val);
	$val = preg_replace('#\s+#u', ' ', $val);

	if (!$bFullPrepare) return $val;

	$val = preg_replace('#\b(\w{1})\b#u', '\\1\\1\\1\\1', $val);
	$val = preg_replace('#\b(\w{2,3})\b#u', '\\1\\1', $val);
	return $val;
}

?>