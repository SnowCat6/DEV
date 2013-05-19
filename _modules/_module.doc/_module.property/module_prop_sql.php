<?
function module_prop_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	//	Найти по родителю
	if (@$val = $search['parent'])
		$search['prop'][':parent'] = alias2doc($val);

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
		//	База данных
		$db			= module('prop');
		//	Все условия свойств
		$thisSQL	= array();
		//	Кеш запросов
		$cacheProps	= getCacheValue('propNameCache');
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();

		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;
			
			$property	= &$cacheProps[$propertyName];
			if (!isset($property)){
				$name = $propertyName;
				makeSQLValue($name);
				
				$db->open("name = $name");
				if ($data = $db->next()){
					$data		= array($db->id(), $data['name'], $data['valueType']);
					$property	= $data;
					setCacheValue('propNameCache', $cacheProps);
				}else{
					$thisSQL= array();
					break;
				}
			}
			
			list($id, $name, $valueType) = $property;
			if ($valueType == 'valueDigit'){
				foreach($values as &$value) $value = (int)$value;
				$values			= implode(',', $values);
				$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
			}else{
				foreach($values as &$value){
					if (!is_string($value)) $value = "$value";
					makeSQLValue($value);
				}
				$values			= implode(',', $values);
				$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
			}
		}

		if ($thisSQL){
			foreach($thisSQL as $id => &$s){
				$sql[] 								= $s;
				$sql[':join']["$table AS a$id"]		= "`doc_id` = a$id.`doc_id`";
				$sql[':join']["$table2 AS vs$id"]	= "vs$id.`values_id` = a$id.`values_id`";
			}
		}else $sql[] = 'false';
	}
}
?>