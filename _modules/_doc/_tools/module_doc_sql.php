<?
function doc2sql($search){
	$sql = array();
	doc_sql($sql, $search);
	return $sql;
}
function doc_sql(&$sql, &$search)
{
	if (!is_array($search)) $search = array();
	//	Подготовить данные для поиска, возможные кастомные обработчики
	$ev = array(&$sql, &$search);
	event('doc.sqlBefore',	$ev);

	$path	= array();
	$db		= module('doc');
	
	if ($val = $search['result']){
		$result	= config::get("searchResult.$val");
		dataMerge($search, $result);
		unset($search['result']);
	}
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

	if (@$val = $search[':title'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`title` IN ($val)";
	}


	if (@$val = $search['template'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`template` IN ($val)";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`doc_type` IN ($val)";
	}

	$val	= $search['dateUpdate'];
	$val2	= $search['dateUpdateTo'];
	if ($val && $val2)
	{
		$val	= dbEncDate($db, $val);
		$val2	= dbEncDate($db, $val2);
		$sql[]	= "`lastUpdate` BETWEEN $val AND $val2";
	}else
	if ($val){
		$val	= dbEncDate($db, $val);
		$sql[]	= "`lastUpdate` >= $val";
	}else
	if ($val2){
		$val2	= dbEncDate($db, $val2);
		$sql[]	= "`lastUpdate` < $val2";
	}
	
	//	Если ищется по имени
	if ($val = @$search['name']){
		$s = array();

		//	Или название / рус, енг
		$v = docPrepareSearch($val);
		$v = trim($v);
		if ($v){
			$ex		= array();	//	Exclude words
//			if (is_int(strpos('вентилятор', $v))) $ex[] = 'обогреватель';
			if ($ex)	$ex = ' -'.implode(' -', $ex);
			else $ex = '';

			$or		= array();
			if (preg_match('#(.*)\(([^\)]+)\)(.*)#', $val, $res))
			{
				$or	= $res[2];
				$v	= $res[1] . $res[3];
				$or	= explode(',', $or);
				foreach($or as &$o) $o = trim(docPrepareSearch($o, false));
				removeEmpty($or);
				if ($or) $or = ' (' . implode(' ', $or) . ')';
				else $or = '';
			}
			
			$name 	= htmlspecialchars(docPrepareSearch($val, false));
			$path[] = "название <b>$name</b>";
			$v 		= str_replace(' ', '* +', $v);
			if ($v) $v = "+$v*";
			
			$v		= trim($v . $ex . $or);
			$s[]	= "MATCH (`searchTitle`) AGAINST ('$v' IN BOOLEAN MODE)";
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

	event('doc.sql',		$ev);
	
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