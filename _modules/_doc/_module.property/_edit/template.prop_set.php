<?
//	+function prop_add
//	Установить знаение свойства документа
function prop_add($db, $docID, $data)
{
	return prop_set($db, $docID, $data, false);
}

//	Установить знаение свойства документа
function prop_set($db, $docID, $data, $bDeleteUnset = true)
{
	$docID	= (int)$docID;
	if (!is_array($data)) return;
	
	$ids	= array();
	$ddb	= module('doc');
	$key	= $db->dbValues->key;
	
	$vCache	= &$GLOBALS['_SETTINGS'][':propCacheValues'];
	if (!$vCache) $vCache = array();
	
	//	Считать все значения свойств, недостающите добавить
	foreach($data as $name => &$prop)
	{
		$prop	= explode(',', $prop);
		foreach($prop as $ix => &$val)
		{
			$val= trim($val);
			if (!$val){
				unset($prop[$ix]);
				continue;
			}
			$d2	= $vCache[$val];
			if ($d2) continue;

			$v 	= dbEncString($db, $val);
			$db->dbValues->open("`valueText` = $v");
			if ($db->dbValues->next()){
				$vCache[$val]	= $db->dbValues->id();
				continue;
			}

			$d2 = array();
			$d2['valueDigit']	= (int)$val;
			$d2['valueText']	= $val;
			$vCache[$val] 		= $db->dbValues->update($d2, false);
		}
	}
	
	//	Все свойства документов
	$props	= array();
	$values	= array();
	$sql	= array();
	$sql[]	= "`doc_id` = $docID";
	$db->dbValue->open($sql);
	while($d = $db->dbValue->next()){
		$ixd	= $db->dbValue->id();;
		$props[$d['prop_id']][$d['values_id']]	= $ixd;
	}
	
	//	Задать значения
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => &$prop)
	{
		$valueType	= 'valueText';		
		$iid		= moduleEx("prop:addName:$name", $valueType);
		$values[$iid]	= $iid;
		if (!$prop)	continue;

		//	Проверить каждое значение свойства
		foreach($prop as &$val)
		{
			//	Получить код значения, если нет то добавить
			$valID	= $vCache[$val];
			//	Если такое значение уже есть, не добавлять
			$vKey	= $props[$iid][$valID];
			if ($vKey){
				unset($props[$iid][$valID]);
				continue;
			}

			$d				= array();
			$d['prop_id']	= $iid;
			$d['doc_id'] 	= $docID;
			$d['values_id']	= $valID;
			$db->dbValue->update($d, false);
			$ids[$docID]= $docID;
		}
	}
	if ($bDeleteUnset)
	{
		$v = array();
		foreach($values as $iid){
			if ($props[$iid]) $v = array_merge($v, $props[$iid]);
		}
		if ($v){
			$db->dbValue->delete($v);
			$ids[$docID]	= $docID;
		}
	}
	if ($ids){
		$ddb->setValue($ids, 'property', NULL);
	}
}
//	+function prop_delete
//	Удалить свойства документа
function prop_delete($db, $docID, $data)
{
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$ddb = module('doc');
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
	
//	$n	= dbEncString($db, $name);
//	$db->open("name = $n");
//	if ($data = $db->next()){
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

?>