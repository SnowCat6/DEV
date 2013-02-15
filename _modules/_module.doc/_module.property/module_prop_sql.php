<?
function prop_sql(&$sql, &$search)
{
	//	Найти по родителю
	if (@$val = $search['parent'])
		$search['prop'][':parent'] = alias2doc($val);

	//	Найти по свойствам
	if (@$val = $search['prop'])
	{
		$bHasPropSQL = false;
		$propNames	= array_keys($val);
		foreach($propNames as &$propName) makeSQLValue($propName);
		$propNames	= implode(',', $propNames);
		
		$md5Val		= hashData($val);
		$propCache	= getCacheValue('propNames');
		$thisSQL 	= &$propCache[$md5Val];
		
		if (!$thisSQL){
			$thisSQL	= array();
			$db			= module('prop');
			$db->open("`name` IN ($propNames)");
			while($data = $db->next())
			{
				$id		= $db->id();
				@$values= $val[$data['name']];
				if (!$values) continue;
				
				$values		= explode(', ', $values);
				$valuesCount= count($values);
				
				if ($data['valueType'] == 'valueDigit'){
					if ($valuesCount > 1){
						foreach($values as &$value) $value = (int)$value;
						$values	= implode(',', $values);
						$s		= "`prop_id` = $id AND `$data[valueType]` IN ($values)";
					}else{
						$value = (int)$values[0];
						$s		= "`prop_id` = $id AND `$data[valueType]` = $value";
					}
				}else{
					if ($valuesCount > 1){
						foreach($values as &$value) makeSQLValue($value);
						$values	= implode(',', $values);
						$s		= "`prop_id` = $id AND `$data[valueType]` IN ($values)";
					}else{
						$value = $values[0];
						makeSQLValue($value);
						$s		= "`prop_id` = $id AND `$data[valueType]` = $value";
					}
				}
	
				$db->dbValue->fields = 'doc_id';
				$s 			= $db->dbValue->makeSQL($s);
				$thisSQL[]	= "`doc_id` IN ($s)";
			}
			if (!$thisSQL) $thisSQL[] = 'true = false';
			
			$thisSQL = implode(' AND ', $thisSQL);
			setCacheValue('propNames', $propCache);
		}
		$sql[] = $thisSQL;
	}
}
?>