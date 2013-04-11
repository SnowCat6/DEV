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

function propFormat($val, &$data, $bUseFormat = true){
	if ($format = $data['format'])
		return $bUseFormat?str_replace('%', "</span>$val<span>", "<span class=\"propFormat\"><span>$format</span></span>"):str_replace('%', $val, $format);
	return $bUseFormat?"<span class=\"propFormat\">$val</span>":$val;
}

function prop_get($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	$res		= array();
	$sql		= array();
	$bNoCache	= false;
	
	$sql[':from']['prop_name_tbl']	= 'p';
	$sql[':from']['prop_value_tbl']	= 'v';
	$db->group	= 'p.`prop_id`';
	$db->order	= 'p.`sort`';
	
	if ($group)
	{
		$group	= explode(',', $group);
/*		foreach($group as &$val){
			makeSQLValue($val);
			$sql[]	= "FIND_IN_SET ($val, p.`group`) > 0";
		}
*/		$bNoCache = true;
	}
	
	if ($docID){
		$docID	= makeIDS($docID);
		$id		= (int)$docID;
		$sql[]	= "v.doc_id IN ($docID)";
		if (count(explode(',', $docID)) != 1) $bNoCache = true;
	}else{
		$bNoCache = true;
	};
	
	if (!$bNoCache){
		$cache = module("doc:cacheGet:$id:property");
		if ($cache) return $cache;
	}
	
	$sql[]		= "p.`prop_id` = v.`prop_id`";
	
	$unuinSQL	= array();
	$sql['type']= "p.`valueType` = 'valueDigit'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT v.`valueDigit` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);
	
	$sql['type']= "p.`valueType` = 'valueText'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT v.`valueText` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);

	$union		= '(' . implode(') UNION (', $unuinSQL) .') ORDER BY sort';
	$db->exec($union);

	while($data = $db->next())
	{
		if ($bNoCache){
			$g = explode(',', $data['group']);
			if (!array_intersect($group, $g)) continue;
		}
		$res[$data['name']] = $data;
	}
	
	if (!$bNoCache){
		m("doc:cacheSet:$id:property", $res);
	}
	
	return $res;
}
function prop_set($db, $docID, $data)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$ids	= $docID;
		$docID	= explode(',', $docID);
	}
	
	if (!is_array($data)) return;
	$a = array();
	setCacheValue('propNames', $a);
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= module("prop:add:$name", &$valueType);
		if (!$iid || !$docID) continue;

		$db->dbValue->exec("DELETE FROM $valueTable WHERE `prop_id` = $iid AND `doc_id` IN ($ids)");
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			$val = trim($val);
			if (!$val) continue;
			
			$d				= array();
			$d['prop_id']	= $iid;
			$d[$valueType]	= $val;
			
			foreach($docID as $doc_id)
			{
				$d['doc_id'] = $doc_id;
				$db->dbValue->update($d, false);
				m("doc:cacheSet:$doc_id:property", NULL);
			}
		}
	}
}

function prop_delete($db, $docID, $dtaa)
{
	$docID	= makeIDS($docID);
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$docID	= explode(',', $docID);
	foreach($docID as $iid){
		m("doc:setCache:$iid:property", NULL);
	}
}

function prop_add($db, $name, &$valueType)
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

function prop_filer(&$prop)
{
	foreach($prop as $name => &$val)
	{
		if ($name[0] != ':') continue;
		if (hasAccessRole('developer')) continue;
		unset($prop[$name]);
	}
}
function prop_value($db, $names, $dtaa)
{
	$ret	= array();
	$names	= explode(',', $names);
	foreach($names as &$name){
		makeSQLValue($name);
	}
	
	$names = implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		$valueType	= $data['valueType'];
		$values		= explode("\r\n", $data['values']);
		foreach($values as $n){
			$n = trim($n);
			if ($n) $ret[$name][$n] = $n;
		}
		
		$db->dbValue->fields= $valueType;
		$db->dbValue->group	= $valueType;
		$db->dbValue->order	= $valueType;
		$db->dbValue->open("`prop_id` = $id");
		while($d = $db->dbValue->next())
		{
			$n = $d[$valueType];
			if ($n) $ret[$name][$n] = $n;
		}
	}
	return $ret;
}
function prop_count($db, $names, &$search)
{
	$ddb		= module('doc');
	$sql		= array();
	$unionSQL	= array();
	
	$table	= $db->dbValue->table();
	$sql[':join']["$table AS p ON p.`doc_id` = `doc_id`"] = "true";
	
	$table	= $db->table();
	if ($names){
		$names	= explode(',', $names);
		foreach($names as &$name) makeSQLValue($name);
		$names	= implode(',', $names);
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueText'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueText'";
	}
	$sql[':join']["$table AS pn ON pn.`prop_id` = p.`prop_id`"] = $thisSQL;
	
	doc_sql($sql, $search);
	
	$ddb->fields= 'pn.`name`, p.`valueText` AS val, count(*) AS cnt';
	$ddb->group	= 'val';
	$unuinSQL[]	= $ddb->makeSQL($sql);
	
	if ($names){
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueDigit'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueDigit'";
	}
	$sql[':join']["$table AS pn ON pn.`prop_id` = p.`prop_id`"] = $thisSQL;
	$ddb->fields= 'pn.`name`, p.`valueDigit` AS val, count(*) AS cnt';
	$unuinSQL[]	= $ddb->makeSQL($sql);

	$union		= '(' . implode(') UNION (', $unuinSQL) . ') ORDER BY name, val';
	
	$ret	= array();
	$ddb->exec($union);
	while($data = $ddb->next()){
		$ret[$data['name']][$data['val']] = $data['cnt'];
	}

	return $ret;
}
function prop_name($db, $group, $data)
{
	$group	= explode(',', $group);
	$ret	= array();
	$db->open();
	while($data = $db->next()){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) continue;
		$ret[$data['name']] = $data;
	}
	return $ret;
}
?>