<?
function prop_sql(&$sql, &$search)
{
	//	Найти по родителю
	if (@$val = $search['parent'])
		$search['prop'][':parent'] = alias2doc($val);

	//	Найти по свойствам
	@$val = $search['prop'];
	if (is_array($val))
	{
		//	База данных
		$db			= module('prop');
		//	Все условия свойств
		$thisSQL	= array();
		//	Кеш запросов
		$cacheProps	= getCacheValue('propNameCache');

		foreach($val as $propertyName => $values)
		{
			$values		= explode(', ', $values);
			if (!$values) continue;
			
			$property	= &$cacheProps[$propertyName];
			if (!isset($property)){
				$name = $propertyName;
				makeSQLValue($name);
				
				$db->open("name = $name");
				if ($data = $db->next()){
					$data = array($db->id(), $data['name'], $data['valueType']);
				}else continue;
				
				$property = $data;
				setCacheValue('propNameCache', $cacheProps);
			}
			
			list($id, $name, $valueType) = $property;
			if ($valueType == 'valueDigit'){
				foreach($values as &$value) $value = (int)$value;
				$values	= implode(',', $values);
				$s		= "a$id.`prop_id` = $id AND a$id.`$valueType` IN ($values)";
			}else{
				foreach($values as &$value) makeSQLValue($value);
				$values	= implode(',', $values);
				$s		= "a$id.`prop_id` = $id AND a$id.`$valueType` IN ($values)";
			}
			$thisSQL[$id]	= $s;
		}

		if ($thisSQL){
			$table	= $db->dbValue->table();
			foreach($thisSQL as $id => $s){
				$sql[':join']["$table AS a$id ON `doc_id` = a$id.`doc_id`"] = $s;
			}
		}else $thisSQL[] = 'false';
	}
}
?>