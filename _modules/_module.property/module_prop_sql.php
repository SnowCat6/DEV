<?
function prop_sql(&$sql, &$search)
{
	//	Найти по родителю
	if (@$val = $search['parent'])
		$search['prop'][':parent'] = $val;

	//	Найти по свойствам
	if (@$val = $search['prop'])
	{
		$propNames	= array_keys($val);
		foreach($propNames as &$propName) makeSQLValue($propName);
		$propNames	= implode(',', $propNames);
		
		$db			= module('prop');
		$thisSQL	= array();
		$db->dbValue->fields = 'doc_id';
		$db->open("`name` IN ($propNames)");
		while($data = $db->next())
		{
			$id		= $db->id();
			@$values= $val[$data['name']];
			if (!$values) continue;
			
			$values = explode(', ', $values);
			
			if ($data['valueType'] == 'valueDigit'){
				foreach($values as &$value) $value = (int)$value;
				$values	= implode(',', $values);
				$s		= "prop_id = $id AND `$data[valueType]` IN ($values)";
			}else{
				foreach($values as &$value) makeSQLValue($value);
				$values	= implode(',', $values);
				$s		= "prop_id = $id AND `$data[valueType]` IN ($values)";
			}

			$thisSQL = $db->dbValue->makeSQL($s);
			$sql[]		= "doc_id IN($thisSQL)";
		}
	}
}
?>