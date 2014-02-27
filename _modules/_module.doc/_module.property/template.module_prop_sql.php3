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
		$cacheProps	= getCacheValue('prop:nameCache');
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();
		//	Пройтись по всем свойствам
		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;
			
			//	Получить свойство из кеша по названию
			$data	= &$cacheProps[$propertyName];
			if (!isset($data))
			{	//	Заполнить кеш
				$name	= $propertyName;
				makeSQLValue($name);
				$db->open("`name` = $name");
				$data	= $db->next();
				setCacheValue('prop:nameCache', $cacheProps);
			}
			
			$db->data	= $data;
			$id	= $db->id();
			//
			$c			= count($sql);
			$queryName	= $data['queryName'];
			$ev			= array(&$db, &$values, &$sql);
			if ($queryName) event("prop.querySQL:$queryName", $ev);
			//	Сформировать условие
			if ($c != count($sql)){
			}else
			switch($data['valueType'])
			{
				case 'valueDigit':
					foreach($values as &$value) $value = (int)$value;
					$values			= implode(',', $values);
					
					$sql[]			= "a$id.`prop_id` = $id AND vs$id.`$data[valueType]` IN ($values)";
					$sql[':join']["$table AS a$id"]		= "`doc_id` = a$id.`doc_id`";
					$sql[':join']["$table2 AS vs$id"]	= "vs$id.`values_id` = a$id.`values_id`";
				break;
				case 'valueText':
					foreach($values as &$value){
						if (!is_string($value)) $value = "$value";
						makeSQLValue($value);
					}
					$values			= implode(',', $values);
					
					$sql[]			= "a$id.`prop_id` = $id AND vs$id.`$data[valueType]` IN ($values)";
					$sql[':join']["$table AS a$id"]		= "`doc_id` = a$id.`doc_id`";
					$sql[':join']["$table2 AS vs$id"]	= "vs$id.`values_id` = a$id.`values_id`";
				break;
			}
		}
	}
}
?>