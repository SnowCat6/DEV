<?
//	function module_prop_add
//	Установить знаение свойства документа
function prop_add($db, $docID, $data)
{
	return prop_set($db, $docID, $data, false);
}

//	Установить знаение свойства документа
function prop_set($db, $docID, $data, $bDeleteUnset = true)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$docIDS	= $docID;
		$docID	= explode(',', $docID);
	}

	if (!is_array($data)) return;
	
	$ids	= array();
	$ddb	= module('doc');
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= moduleEx("prop:addName:$name", $valueType);
		if (!$iid || !$docID) continue;

		$props	= array();
		$propsID= array();
		//	Все свойства документов
		$sql	= array();
		$sql[]	= "`prop_id` = $iid AND `doc_id` IN ($docIDS)";
		$db->dbValue->open($sql);
		while($d = $db->dbValue->next()){
			//	Создать массиво имеющихся свойств
			//	doc_id:value => id
			$key	= "$d[doc_id]:$d[values_id]";
			$ixd	= $db->dbValue->id();
			$props[$key]	= $ixd;
			$propsID[$ixd]	= $ixd;
		}
		//	Проверить каждое значение свойства
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			$val = trim($val);
			if (!$val){
				$db->dbValue->delete("doc_id IN ($docID) AND prop_id = `$iid`");
				$ddb->setValue($docID, 'property', NULL);
				continue;
			}
			
			$v = $val; makeSQLValue($v);
			$db->dbValues->open("`valueText` = $v");
			$d 			= $db->dbValues->next();
			$valuesID	= $db->dbValues->id();
			if (!$valuesID || $d[$valueType] != $val)
			{
				$d2 				= array();
				$d2['id']			= $valuesID;
				$d2['valueDigit']	= (int)$val;
				$d2['valueText']	= $val;
				$valuesID = $db->dbValues->update($d2, false);
			}

			foreach($docID as $doc_id)
			{
				//	Если такое значение уже есть, не добавлять
				$key = "$doc_id:$valuesID";
				if (@$ixd = $props[$key]){
					unset($propsID[$ixd]);
				}else{
					$d				= array();
					$d['prop_id']	= $iid;
					$d['doc_id'] 	= $doc_id;
					$d['values_id']	= $valuesID;
//					$d['valueInt']	= (int)$val;
					$ixd = $db->dbValue->update($d, false);
					$props[$key]	= $ixd;
				}
				$ids[$doc_id] = $doc_id;
			}
		}
		if ($ids){
			$ddb->setValue($ids, 'property', NULL);
		}
		if ($propsID && $bDeleteUnset){
			$db->dbValue->delete($propsID);
		}
	}
}
//	function module_prop_delete
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
	$n		= $name; makeSQLValue($n);

	$db->open("name = $n");
	if ($data = $db->next()){
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