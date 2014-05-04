<?
function doc2sql($search){
	$sql = array();
	doc_sql($sql, $search);
	return $sql;
}
function doc_sql(&$sql, &$search)
{
	$db		= module('doc');
	$path	= array();
	///////////////////////////////////////////
	//	Найти по номеру документа
	if (isset($search['id']))
	{
		$val	= $search['id'];
		if (is_string($val)){
			$v	= alias2doc($val);
			if (!$v) $v = makeIDS($val);
			$val= $v;
		}else $val	= makeIDS($val);
		
		if ($val) $sql[]	= "`doc_id` IN ($val)";
		else $sql[] = 'false';
	}

	if (@$val = $search['title'])
	{
		$val	= $db->escape_string($val);
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

	$val = @$search['dateUpdate'];
	$val2 = @$search['dateUpdateTo'];
	if ($val && $val2)
	{
		$val	= makeSQLDate($val);
		$val2	= makeSQLDate($val2);
		$sql[]	= "`lastUpdate` BETWEEN $val AND $val2";
	}else
	if ($val){
		$val	= makeSQLDate($val);
		$sql[]	= "`lastUpdate` >= $val";
	}else
	if ($val2){
		$val2	= makeSQLDate($val2);
		$sql[]	= "`lastUpdate` < $val2";
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

	$ev = array(&$sql, &$search);
	event('doc.sql', $ev);
	
	if (@$sql[':from'] || @$sql[':join']){
		$sql[':from'][] = 'd';
	}

	return $path;
}
//	Убрать все неиндексируемые символы, одиночные буквы и цифры расщирить до 4х знаков
function docPrepareSearch($val, $bFullPrepare = true)
{
	$val = strip_tags($val);
	$val = preg_replace('#&(\w+);#', ' ', $val);
	$val = preg_replace('#[^a-zа-я\d]#iu', ' ', $val);
	$val = preg_replace('#\s+#u', ' ', $val);

	if (!$bFullPrepare) return $val;

	$val = preg_replace('#\b(\w{1})\b#u', '\\1xyz',	$val);
	$val = preg_replace('#\b(\w{2})\b#u', '\\1yz',	$val);
	$val = preg_replace('#\b(\w{3})\b#u', '\\1z',	$val);
	
	//	65kb maximum TEXT field length
	//	FULLTEXT index possible only with TEXT fueld
	$val = substr($val, 0, 65000);
	
	return $val;
}

?>