<?
function module_prop_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	
	//	Найти все названия начинающиеся с @ и сделать их свойствами
	foreach($search as $name=>$v)
	{
		if ($name[0] != '@') continue;
		unset($search[$name]);
		$name	= substr($name, 1);
		$search['prop'][$name]	= $v;
	}
	
	//	Найти по родителю
	if (@$val = $search['parent']){
		$search['prop'][':parent'] = alias2doc($val);
	}

	//	Со всеми додкаталогами
	if (@$val = $search['parent*']){
		$search['prop']['parent*'] = $val;
	}
	if (@$val = $search['prop']['parent*'])
	{
		$type	='';
		unset($search['prop']['parent*']);
		
		if (is_array($val)){
			$id = explode(',', makeIDS($val));
		}else{
			@list($id, $type) = explode(':', $val);
			$id = alias2doc($id);
		}
		if ($id){
			$db		= module('doc');

			$tree	= module('doc:childs:5', array('parent' => $id, 'type' => $type?$type:'page,catalog'));
			$search['prop'][':parent']	= array($id);
			getSearchTreeChilds($tree, $search['prop'][':parent']);
		}else $sql[] = 'false';
	}
	if (isset($search['prop'][':parent']) && !is_array($search['prop'][':parent'])){
		$search['prop'][':parent'] = explode(',', makeIDS($search['prop'][':parent']));
	}
	
	//	Найти по свойствам
	@$val = $search['prop'];
	if ($val && is_array($val))
	{
		$bHasSQL	= false;
		//	База данных
		$db			= module('prop');
		//	Все условия свойств
		$thisSQL	= array();
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();
		//	Объедененные условия поиска среди свойств
		$sql[':IN']		= array();
		//	Пройтись по всем свойствам
		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;

			//	Плагин обработки функций
			$ev	= array(&$db, &$values, &$sql, &$propertyName);
			event("prop.querySQLfnBefore",	$ev);
			event("prop.querySQLfn",		$ev);
			if (!$propertyName) continue;

			//	Получить свойство из кеша по названию
			$data	= propertyGetInt($db, $propertyName);
			//	Выполнить кастомный запрос свойств
			if ($queryName = $data['queryName'])
			{
				$ev		= array(&$db, &$values, &$sql);
				event("prop.querySQL:$queryName", $ev);
				continue;
			}
			//	Сформировать условие
			$id	= $db->id();
			$valueType	= $data['valueType'];
			//	Кодировать каждое значения для использования в поиске
			foreach($values as &$value){
				$value = intPropEnc($db, $valueType, $value);
			}
			//	Сформировать запрос
			$c2		= count($values);
			$values	= implode(',', $values);
			//	Оптимизировать запрос
			if ($c2 > 1) $sql[':IN'][$id][]	= "pv.`$valueType` IN ($values)";
			else $sql[':IN'][$id][]	= "pv.`$valueType` = $values";
		}
		//	Объеденить запросы к свойствам
		$in	= $sql[':IN'];
		unset($sql[':IN']);
		//	Если есть специальный подзапрос выборки по свойствам, то сформируем выборку
		if ($in){
			//	Объеденить все подзапросы оператором OR, т.е. выбрать все занчения свойств
			$c	= count($in);
			$or	= array();
			foreach($in as $iid => $q){
				$q2	= implode(' OR ', $q);
				if (count($q) > 1) $or[]	= "prop_id=$iid AND ($q2)";
				else $or[]	= "prop_id=$iid AND $q2";
			}
			$or	= implode(') OR (', $or);
			//	Выбрать свойства и оставить только те документы, у которых выбранных свойст такое же количество как и в запросе
			//	Если в запросе одно свойтсвет, то сформировать оптимизированный запрос
			if ($c > 1){
				$s	= "SELECT doc_id FROM $table AS p, $table2 AS pv WHERE p.`values_id`=pv.`values_id` AND (($or)) GROUP BY doc_id, prop_id";
				$s	= "SELECT doc_id FROM ($s) AS gPropIDS GROUP BY doc_id HAVING count(*)=$c";
			}else{
				$s	= "SELECT doc_id FROM $table AS p, $table2 AS pv WHERE p.`values_id`=pv.`values_id` AND $or";
			}

			$sql[':from']["($s)"]	= 'propIDS';
			$sql[]					= '`doc_id`=propIDS.`doc_id`';
		}
	}
}
function prop_fnSQLbetween(&$db, &$val, &$ev)
{
	$propertyName	= &$ev[3];
	//	BETWEEN
	//	Выборка по двум свойствам в диапазоне которых находится значение
	if (strncmp(':between:', $propertyName, 9)) return;

	$values	= &$ev[1];
	$sql	= &$ev[2];
	//	Разделить на названия свойств
	$propertyName	= substr($propertyName, 9);
	$propertyName	= propSplit($propertyName);
	//	Получить идентификаторы свойств
	$pFrom	= propertyGetInt($db, $propertyName[0]);
	$pFromID= $db->id();
	$pTo	= propertyGetInt($db, $propertyName[1]);
	$pToID	= $db->id();
	//	Добавть два значения для нижней и верхней границы
	$valueType	= $pFrom['valueType'];
	$value		= intPropEnc($db, $valueType,	$values[0]);
	$sql[':IN'][$pFromID][]	= "pv.`$valueType`<= $value";
	$sql[':IN'][$pToID][]	= "pv.`$valueType` > $value";
	
	$propertyName	= '';
}
function prop_fnSQLperiod(&$db, &$val, &$ev)
{
	$propertyName	= &$ev[3];
	//	PERIOD
	//	Выборка по двум свойствам в диапазоне которых находится значение
	if (strncmp(':period:', $propertyName, 8)) return;

	$values	= &$ev[1];
	$sql	= &$ev[2];
	//	Разделить на названия свойств
	$propertyName	= substr($propertyName, 8);
	//	Получить идентификаторы свойств
	$p	= propertyGetInt($db, $propertyName);
	$pID= $db->id();
	$valueType	= $p['valueType'];
	
	list($v1, $v2)	= explode('-', $values[0]);
	if ($v1 && $v2){
		$v1	= intPropEnc($db, $valueType, $v1);
		$v2	= intPropEnc($db, $valueType, $v2);
		$v	= "BETWEEN $v1 AND $v2";
	}else if ($v1){
		$v1	= intPropEnc($db, $valueType, $v1);
		$v	= " > $v1";
	}else{
		$v2	= intPropEnc($db, $valueType, $v2);
		$v	= " < $v2";
	}
	$sql[':IN'][$pID][]	= "pv.`$valueType` $v";

	$propertyName	= '';
}

function getSearchTreeChilds($tree, &$childs)
{
	foreach($tree as $id => $c)
	{
		if (!is_int($id)) continue;
		$childs[$id] = $id;
		if ($c) getSearchTreeChilds($c, $childs);
	}
}
?>