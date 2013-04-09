<?
function prop_sql(&$sql, &$search)
{
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
			$s	= array();
			
			if (!is_array($id)) $id = explode(',', makeIDS($id));
			$ids = $id;
			while(true){
				$s['prop'][':parent'] = implode(',', $ids);
				if ($type) $s['type'] = $type;
				$ids = $db->selectKeys('doc_id', doc2sql($s));
				$ids = array_diff(explode(',', $ids), $id);
				if (!$ids) break;
				$id = array_merge($id, $ids);
			};
			$search['prop'][':parent'] = implode(', ', $id);
		}else $sql[] = 'false';
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
				$values		= implode(',', $values);
				$thisSQL[$id]="a$id.`prop_id` = $id AND a$id.`$valueType` IN ($values)";
			}else{
				foreach($values as &$value) makeSQLValue($value);
				$values		= implode(',', $values);
				$thisSQL[$id]="a$id.`prop_id` = $id AND a$id.`$valueType` IN ($values)";
			}
		}

		if ($thisSQL){
			$table	= $db->dbValue->table();
			foreach($thisSQL as $id => $s){
				$sql[':join']["$table AS a$id ON `doc_id` = a$id.`doc_id`"] = $s;
			}
		}else $sql[] = 'false';
	}
}
?>