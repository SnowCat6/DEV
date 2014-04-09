<?
function module_prop_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	//	Найти по родителю
	if (@$val = $search['parent']){
		$search['prop'][':parent'] = alias2doc($val);
	}

	//	Со всеми додкаталогами
	if (@$val = $search['parent*'])
	{
		@list($id, $type) = explode(':', $val);
		$id = alias2doc($id);
		if ($id){
			$db	= module('doc');
			
			if (!is_array($id)) $id = explode(',', makeIDS($id));
			$s	= array();
			$ids= $id;
			while(true){
				$s['prop'][':parent'] = $ids;
				if ($type) $s['type'] = $type;
				$ids = $db->selectKeys('doc_id', doc2sql($s));
				if (!$ids) break;
				$ids = array_diff(explode(',', $ids), $id);
				if (!$ids) break;
				$id = array_merge($id, $ids);
			};
			$search['prop'][':parent'] = implode(', ', $id);
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
		//	Кеш запросов
		$cacheProps	= getCache('prop:nameCache');
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();
		$sql[':IN']	= array();
		//	Пройтись по всем свойствам
		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;
			
			//	Получить свойство из кеша по названию
			$data	= $cacheProps[$propertyName];
			if (!isset($data))
			{	//	Заполнить кеш
				$name	= $propertyName;
				makeSQLValue($name);
				$db->open("`name` = $name");
				$cacheProps[$propertyName]	= $data	= $db->next();
				setCache('prop:nameCache', $cacheProps);
			}else{
				$db->data	= $data;
			}
			
			$id			= $db->id();
			//
			$c			= count($sql) + count($sql[':IN']);
			$queryName	= $data['queryName'];
			//	Выполнить кастомный запрос свойств
			if ($queryName){
				$ev		= array(&$db, &$values, &$sql);
				event("prop.querySQL:$queryName", $ev);
			}
			//	Сформировать условие
			if ($c != count($sql) + count($sql[':IN'])){
				//	Пропустить, т.к. запрос был обработан внешним обработчиком
			}else
			switch($data['valueType'])
			{
				//	Обработать цифровые значения
				case 'valueDigit':
					foreach($values as &$value) $value = (int)$value;
					$c2				= count($values);
					$values			= implode(',', $values);
					if ($c2 > 1) $sql[':IN'][]	= "p.`prop_id`=$id AND pv.`$data[valueType]` IN ($values)";
					else $sql[':IN'][]	= "p.`prop_id`=$id AND pv.`$data[valueType]`=$values";
				break;
				//	Обработать текстовые значения
				case 'valueText':
					foreach($values as &$value){
						if (!is_string($value)) $value = "$value";
						makeSQLValue($value);
					}
					$c2				= count($values);
					$values			= implode(',', $values);
					if ($c2 > 1) $sql[':IN'][]	= "prop_id=$id AND pv.`$data[valueType]` IN ($values)";
					else $sql[':IN'][]	= "prop_id=$id AND pv.`$data[valueType]` = $values";
				break;
			}
		}
		
		$in	= $sql[':IN'];
		unset($sql[':IN']);
		//	Если есть специальный подзапрос выборки по свойствам, то сформируем выборку
		if ($in){
			//	Объеденить все подзапросы оператором OR, т.е. выбрать все занчения свойств
			$c	= count($in);
			$or	= implode(') OR (', $in);
			//	Выбрать свойства и оставить только те документы, у которых выбранных свойст такое же количество как и в запросе
			//	Если в запросе одно свойтсвет, то сформировать оптимизированный запрос
			$ids	= array();
			if ($c > 1){
				$s	= "SELECT doc_id FROM $table AS p, $table2 AS pv WHERE p.`values_id`=pv.`values_id` AND (($or)) GROUP BY doc_id HAVING count(*)=$c";
			}else{
				$s	= "SELECT doc_id FROM $table AS p, $table2 AS pv WHERE p.`values_id`=pv.`values_id` AND $or";
			}
			$sql[':join']["($s) AS ids"]	= '`doc_id`=ids.`doc_id`';
		}
	}
}
?>