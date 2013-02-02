<?
function module_prop($fn, &$data)
{
	//	База данных пользователей
	$db	= new dbRow('prop_name_tbl', 'prop_id');
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');

	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("prop_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function prop_get($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	$sql	= array();
	$res	= array();
	$prop	= array();
	
	if ($group){
		$group	= explode(',', $group);
		foreach($group as &$val) makeSQLValue($val);
		$group	= implode(',', $group);
		$sql[]	= "group IN ($group)";
	}
	
	if ($docID){
		$docID	= makeIDS($docID);
		$ids	= array();
		$db->dbValue->open("doc_id IN ($docID)");
		while($data = $db->dbValue->next()){
			$ids[] = $data['prop_id'];
			$prop[$data['prop_id']][$db->dbValue->id()] = $data;
		}
		$ids	= implode(',', $ids);
		$sql[]	= "prop_id IN ($ids)";
	}

	$db->open($sql);
	while($data = $db->next())
	{
		$p	= array();
		if (@$propData = $prop[$db->id()])
		{
			$valueType	= $data['valueType'];
			foreach($propData as $iid => &$val) $p[$iid] = $val[$valueType];
		}
		$data['property'] 	= implode(', ', $p);
		$res[$data['name']]	= $data;
	}

	return $res;
}
function prop_set($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	if ($docID){
		$docID = makeIDS($docID);
		$db->dbValue->deleteByKey('doc_id', $docID);
		$docID	= explode(',', $docID);
	}
	
	if (!is_array($data)) return;
	
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= prop_add($db, $name, &$valueType);
		if (!$iid || !$docID) continue;
		
		$propSet= array();
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			if (!$val) return;
			
			$d				= array();
			$d['prop_id']	= $iid;
			$d[$valueType]	= $val;
			
			foreach($docID as $doc_id)
			{
				if (isset($propSet["$doc_id:$val"])) continue;
				$propSet["$doc_id:$val"] = true;
				
				$d['doc_id'] = $doc_id;
				$db->dbValue->update($d, false);
			}
		}
	}
}

function prop_add($db, $name, &$valueType)
{
	if (!$name) return;
	
	if (!$valueType) $valueType = 'valueText';
	$n		= $name; makeSQLValue($n);
	
	$db->open("name = $n");
	if ($data = $db->next()){
		$iid = $db->id();
		$valueType = $data['valueType'];
	}else{
		$d			= array();
		$d['name']	= $name;
		$d['valueType'] = $valueType;
		$iid		= $db->update($d, false);
	}
	
	return $iid;
}
?>