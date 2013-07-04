<?
function module_prop($fn, &$data)
{
	//	База данных пользователей
	$db	= new dbRow('prop_name_tbl', 'prop_id');
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');
	$db->dbValues= new dbRow('prop_values_tbl','values_id');

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
	
	$bNoCache	= false;
	
	$docID	= makeIDS($docID);
	$ids	= explode(',', $docID);
	if (count($ids) != 1) $bNoCache = true;
	
	if ($group)	$group	= explode(',', $group);
	else $group = array();
	
	if (!$bNoCache)
	{
		$ddb	= module('doc');
		$data	= $ddb->openID($docID);
		if (!$data) return array();

		@$res	= unserialize($data['property']);
		if (is_array($res))
		{
			if (!$group) return $res;
			foreach($res as $name => &$data){
				$g = explode(',', $data['group']);
				if (!array_intersect($group, $g)) unset($res[$name]);
			}
			return $res;
		}
	}
	
	$res	= array();
	$sql	= array();
	$sql[]	= "v.`doc_id` IN ($docID)";
	$sql[':from']['prop_name_tbl']	= 'p';
	$sql[':from']['prop_value_tbl']	= 'v';
	$table2	= $db->dbValues->table();
	$sql[':join']["$table2 AS vs"]	= 'vs.`values_id` = v.`values_id`';
	$sql[]		= "p.`prop_id` = v.`prop_id`";
	$db->group	= 'p.`prop_id`';
	$db->order	= 'p.`sort`';

	$unuinSQL	= array();
	$sql['type']= "p.`valueType` = 'valueDigit'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueDigit` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);
	
	$sql['type']= "p.`valueType` = 'valueText'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueText` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);

	$union		= '(' . implode(') UNION (', $unuinSQL) .') ORDER BY `sort`';
	$db->exec($union);

	while($data = $db->next())
	{
		if ($bNoCache){
			$g = explode(',', $data['group']);
			if (!array_intersect($group, $g)) continue;
		}
		$res[$data['name']] = $data;
	}
	
	if ($bNoCache) return $res;

	$ddb->setValue($docID, 'property', $res, false);
	if (!$group) return $res;
	
	foreach($res as $name => &$data){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) unset($res[$name]);
	}
	
	return $res;
}
function prop_set($db, $docID, $data)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$docIDS	= $docID;
		$docID	= explode(',', $docID);
	}
	
	if (!is_array($data)) return;
	
	$a	= array();
	setCacheValue('propNames', $a);
	$ids= array();
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= module("prop:add:$name", &$valueType);
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
			if (!$val) continue;
			
			if ($valueType == 'valueDigit'){
				$v = (int)$val;
			}else{
				$v = $val; makeSQLValue($v);
			}
			$db->dbValues->open("`$valueType` = $v");
			$d = $db->dbValues->next();
			if (!$d){
				$d = array();
				$d['valueDigit']= (int)$val;
				$d['valueText']	= $val;
				$valuesID = $db->dbValues->update($d, false);
			}else{
				$valuesID = $db->dbValues->id();
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
					$ixd = $db->dbValue->update($d, false);
					$props[$key]	= $ixd;
				}
				$ids[$doc_id] = $doc_id;
//				m("doc:cacheSet:$doc_id:property", NULL);
			}
		}
		if ($ids){
			$ddb = module('doc');
			$ddb->setValue($ids, 'property', NULL);
		}
		if ($propsID)	$db->dbValue->delete($propsID);
	}
}

function prop_delete($db, $docID, $data)
{
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$ddb = module('doc');
	$ddb->setValue($docID, 'property', NULL);
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
	$ddb	= module('doc');

	$ret	= array();
	$union	= array();

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();

	$names	= explode(',', $names);
	foreach($names as &$name) makeSQLValue($name);
	$names	= implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		makeSQLValue($name);
		$sort	= $data['sort'];
		$sort2	= 0;

		$queryName	= $data['queryName'];
		$ev			= array(&$data['query'], array());
		if ($queryName) event("prop.query:$queryName", $ev);

		if ($query = &$ev[1]){
			foreach($query as $n => $q){
				makeSQLValue($n);
				$sql		= array();
				$sql[]		= $q;
				$ddb->fields= "$name AS name, $n AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
				$ddb->group	= '';

				doc_sql(&$sql, $search);
				$union[]	= $ddb->makeSQL($sql);
				++$sort2;
			}
		}else{
			$sql	= array();
			$sql[':join']["$table AS p$id"]		= "p$id.`doc_id` = `doc_id`";
			$sql[':join']["$table2 AS pv$id"]	= "p$id.`values_id` = pv$id.`values_id`";
			$ddb->group		= "pv$id.`values_id`";
			$sql[':where']	= "p$id.`prop_id`=$id";
			
			$ddb->fields	= "$name AS name, pv$id.`$data[valueType]` AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
			
			doc_sql(&$sql, $search);
			$union[]	= $ddb->makeSQL($sql);
		}
	}
	$union	= '(' . implode(') UNION (', $union) . ') ORDER BY `sort`, `sort2`';
	$ddb->exec($union);
	while($data = $ddb->next()){
		$count	= $data['cnt'];
		if ($count) $ret[$data['name']][$data['value']] = $count;
	}
	
	return $ret;
	
	$ddb		= module('doc');
	$sql		= array();
	$unionSQL	= array();
	
	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql[':join']["$table AS p"]	= 'p.`doc_id` = `doc_id`';
	$sql[':join']["$table2 AS vs"]	= 'vs.`values_id` = p.`values_id`';
	
	$table	= $db->table();
	if ($names){
		$names	= explode(',', $names);
		foreach($names as &$name) makeSQLValue($name);
		$names	= implode(',', $names);
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueText'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueText'";
	}
	$sql2	= $sql;
	$sql2[':join']["$table AS pn"] = 'pn.`prop_id` = p.`prop_id`';
	$sql2[]	= $thisSQL;
	doc_sql($sql2, $search);
	
	$ddb->fields= 'pn.`name`, pn.`sort`, vs.`valueText` AS val, count(*) AS cnt';
	$ddb->group	= 'val';
	$unuinSQL[]	= $ddb->makeSQL($sql2);
	
	if ($names){
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueDigit'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueDigit'";
	}
	$sql2	= $sql;
	$sql2[':join']["$table AS pn"] = 'pn.`prop_id` = p.`prop_id`';
	$sql2[]		= $thisSQL;
	doc_sql($sql2, $search);

	$ddb->fields= 'pn.`name`, pn.`sort`, vs.`valueDigit` AS val, count(*) AS cnt';
	$ddb->group	= 'val';
	$unuinSQL[]	= $ddb->makeSQL($sql2);

	$union		= '(' . implode(') UNION (', $unuinSQL) . ') ORDER BY `sort`, `name`, `val`';
	
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
function prop_clear($db, $id, $data)
{
	if ($id){
		$ids		= makeIDS($id);
		$ddb		= module('doc');
		$table		= $db->dbValue->table();
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable AS d INNER JOIN $table AS p ON d.`doc_id` = p.`doc_id` SET `property` = NULL  WHERE p.`prop_id` IN ($ids)";
		$ddb->exec($sql);
	}else{
		$ddb		= module('doc');
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable SET `property` = NULL";
		$ddb->exec($sql);
	}

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql	= "DELETE vs FROM $table2 AS vs WHERE `values_id` NOT IN (SELECT `values_id` FROM $table)";
	$db->exec($sql);
	
	$dbDoc		= module('doc');
	$docTable	= $dbDoc->table();
	$sql		= "DELETE v FROM $table AS v WHERE `doc_id` NOT IN (SELECT doc_id FROM $docTable)";
	$db->exec($sql);
}
function prop_addQuery($db, $query, $queryName)
{
	$q	= getCacheValue('propertyQuery');
	if (!is_array($q)) $q = array();
	$q[$query] = $queryName;
	setCacheValue('propertyQuery', $q);
}
?>