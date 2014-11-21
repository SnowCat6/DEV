<?
//	Установить знаение свойства документа
//	+function prop_add
function prop_add($db, $docID, $property)
{
	$docID	= (int)$docID;
	if (!is_array($property)) return;
	//	Получить идентификаторы всех значений, удалить пустые свойства, заменить значения на идентификаторы
	propPrepareValues($db, $property);
	//	Получить все свойства документа, заполнить кеш
	propGetPropByID($db, $docID);
	//	Задать значения
	foreach($property as $name => &$prop2)
	{
		$valueType		= 'valueText';		
		$propID			= moduleEx("prop:addName:$name", $valueType);
		//	Проверить каждое значение свойства
		foreach($prop2 as $valuesID)
		{
			propSetPropByID($db, $docID, $propID, $valuesID);
		}
	}
}

//	Установить знаение свойства документа
//	+function prop_set
function prop_set($db, $docID, $property)
{
	$bUpdate	= false;
	$docID		= (int)$docID;
	if (!is_array($property)) return;
	
	//	Получить идентификаторы всех значений, удалить пустые свойства, заменить значения на идентификаторы
	propPrepareValues($db, $property);
	//	Получить все свойства документа
	propGetPropByID($db, $docID);
	
	global $_CONFIG;
	$props	= &$_CONFIG[':propCacheSetValues'][$docID];
	
	//	Задать значения
	$v	= array();
	foreach($property as $name => &$prop2)
	{
		$valueType	= 'valueText';		
		$propID		= moduleEx("prop:addName:$name", $valueType);
		//	Проверить каждое значение свойства
		$v[$propID]	= array();
		foreach($prop2 as $valuesID)
		{
			$valueIDs	= $props[$propID][$valuesID];
			if ($valueIDs){
				$valueID	= propSetPropByID($db, $docID, $propID, $valuesID);
			}else{
				$valueID	= propSetPropByID($db, $docID, $propID, $valuesID);
				$bUpdate	= true;
			}
			$v[$propID][$valueID]= $valueID;
		}
	}

	//	Удплить не заданные значения
	$ids	= array();
	foreach($props as $propID => &$valuesIDs)
	{
		if (!isset($v[$propID])) continue;

		foreach($valuesIDs as $valuesID => &$valueIDs){
			foreach($valueIDs as $valueID)
			{
				if ($v[$propID][$valueID]) continue;
				unset($props[$propID][$valuesID][$valueID]);
				$ids[]	= $valueID;
			}
		}
	}
	if ($ids){
		$table	= $db->dbValue->table();
		$ids	= makeIDS($ids);
		$sql	= "DELETE FROM $table WHERE value_id IN ($ids)";
		$db->dbValue->exec($sql);
		$bUpdate= true;
	}

	//	Обновить документ, если были изменения
	if ($bUpdate){
		$ddb	= module('doc');
		$ddb->setValue($docID, 'property', NULL);
	}
}
//	Установить знаение свойства документа
//	+function prop_unset
function prop_unset($db, $docID, $data)
{
	$docID	= (int)$docID;
	if (!is_array($data)) return;
	
	$sql	= array();
	foreach($data as $name => $values)
	{
		$q		= array();
		$values	= explode(',', $values);
		foreach($values as $val){
			$v = trim($val);
			if (!$v) continue;
			
			$v		= dbEncString($db, $v);
			$q[$val]= $v;
		}
		if (!$q) continue;
		
		$name	= dbEncString($db, $name);
		$q		= implode(',', $q);
		$sql[]	= "(pn.`name` = $name AND pv.`valueText` IN ($q))";
	}
	if (!$sql) return;
	
	$pTable	= $db->dbValue->table();
	$pvTable= $db->dbValues->table();
	$pnTable= $db->table();

	$sql	= implode(' OR ', $sql);
	$sql	= "p.`doc_id` = $docID AND p.`prop_id` = pn.`prop_id` AND p.`values_id` = pv.`values_id` AND ($sql)";
	$sql	= "SELECT p.`value_id` FROM $pTable AS p, $pnTable AS pn, $pvTable AS pv WHERE $sql";
	
	$ids	= array();
	$db->exec($sql);
	while($data = $db->next()){
		$ids[]	= $data['value_id'];
	}
	$ids	= makeIDS($ids);
	if (!$ids) return;
	
	$sql	= "DELETE FROM $pTable WHERE `value_id` IN ($ids)";
	$db->exec($sql);
	
	$ddb	= module('doc');
	$ddb->setValue($docID, 'property', NULL);
}
//	+function prop_delete
//	Удалить свойства документа
function prop_delete($db, $docID, $data)
{
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$ddb	= module('doc');
	$ddb->setValue($docID, 'property', NULL);
}

function prop_addName($db, $name, &$valueType)
{
	$name		= trim($name);
	@$aliases	= &$GLOBALS['_CONFIG']['propertyAliases'];
	if (!is_array($aliases)){
		$aliases = array();
		$db->open();
		while($data = $db->next()){
			$alias = explode("\r\n", $data['alias']);
			foreach($alias as $key) $aliases[strtolower($key)] = $data['name'];
		}
	}
	@$alias = trim($aliases[strtolower($name)]);
	if ($alias) $name = $alias;
	
	if (!$valueType) $valueType = 'valueText';
	
	if ($data = propertyGetInt($db, $name)){
		$iid		= $db->id();
		$valueType	= $data['valueType'];
	}else{
		$d			= array();
		$d['name']	= $name;
		$d['valueType'] = $valueType;
		$d['group']	= $group;
		$iid		= $db->update($d, false);
	}
	
	return $iid;
}
//	Прочитать все значения в массиве свойств
function propPrepareValues(&$db, &$property)
{
	//	Получить ссылку на кеш свойств
	global $_CONFIG;
	$vCache	= &$_CONFIG[':propCacheValues'];
	if (!$vCache) $vCache = array();

	$newVal	= array();
	//	Считать все значения свойств, недостающите добавить
	foreach($property as $name => &$prop)
	{
		//	Разделить свойства
		$pass	= array();
		$prop	= explode(',', $prop);
		foreach($prop as $ix => $val)
		{
			//	Удалить пробелы
			$val	= trim($val);
			//	Если значение есть в кеше, пропустить
			if ($val && !$pass[$val])
			{
				$prop[$ix]		= $val;
				$pass[$val]		= $val;
				//	Если есть в кеше, пропустить
				if ($vCache[$val]) continue;
				//	Если есть значение, отложить для массового считывания
				$newVal[$val]	= dbEncString($db, $val);
			}else{
				//	Если пустое значение, удалить
				unset($prop[$ix]);
			}
		}
	}
	//	Если есть нераспознанные значения, прочитать из БД
	if ($newVal)
	{
		$v	= implode(',', $newVal);
		$db->dbValues->open("`valueText` IN ($v)");
		while($data = $db->dbValues->next())
		{
			$val			= $data['valueText'];
			$vCache[$val]	= $db->dbValues->id();
			//	Удалить из списка считанное знечение
			unset($newVal[$val]);
		}
		//	Добавить недостающие значения
		foreach($newVal as $val => $v)
		{
			$d2 = array();
			$d2['valueDigit']	= (int)$val;
			$d2['valueText']	= $val;
			$d2['valueDate']	= makeDateStamp($val);
			$vCache[$val] 		= $db->dbValues->update($d2, false);
		}
	}
	//	Заменить значения на идентификаторы
	foreach($property as $name => &$prop2){
		foreach($prop2 as $ix => $val2){
			$val2		= $vCache[$val2];
			$prop2[$ix]	= $val2;
		}
	}
}
function propSetPropByID(&$db, $docID, $propID, $valuesID)
{
	//	Все свойства документов
	global $_CONFIG;
	$pCache	= &$_CONFIG[':propCacheSetValues'];
	$props	= &$pCache[$docID];
	if (!is_array($props)) $props = array();
	
	$valueID	= $props[$propID][$valuesID];
	if ($valueID)	return end($valueID);

	$d				= array();
	$d['doc_id'] 	= $docID;
	$d['prop_id']	= $propID;
	$d['values_id']	= $valuesID;

	$valueID		= $db->dbValue->update($d, false);
	$props[$propID][$valuesID][$valueID]	= $valueID;

	return $valueID;
}
function propGetPropByID(&$db, $docID)
{
	//	Все свойства документов
	global $_CONFIG;
	$pCache	= &$_CONFIG[':propCacheSetValues'];
	$props	= &$pCache[$docID];
	if (is_array($props)) return $props;

	$props	= array();
	$sql	= array();
	$sql[]	= "`doc_id` = $docID";
	$db->dbValue->open($sql);
	while($d = $db->dbValue->next())
	{
		$valueID	= $db->dbValue->id();
		$propID		= $d['prop_id'];
		$valuesID	= $d['values_id'];
		
		$props[$propID][$valuesID][$valueID]	= $valueID;
	}

	return $props;
}
?>