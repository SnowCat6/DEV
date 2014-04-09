<?
function module_prop($fn, &$data)
{
	//	Таблица данных свойств
	$db			 = new dbRow('prop_name_tbl', 'prop_id');
	//	Таблица связывает документы,свойства и значения свойств
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');
	//	Таблица значеий свойств
	$db->dbValues= new dbRow('prop_values_tbl','values_id');

	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("prop_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function propSplit(&$prop){
	return preg_split('#,(?!\s)#', $prop);
}
function propFormat($val, &$data, $bUseFormat = true){
	if ($format = $data['format']){
		if ($bUseFormat){
			$v = str_replace('%', "</span>$val<span>", "<span class=\"propFormat\"><span>$format</span></span>");
			return str_replace('<span></span>', '', $v);
		}else{
			return str_replace('%', $val, $format);
		}
	}
	return $bUseFormat?"<span class=\"propFormat\">$val</span>":$val;
}
//	Полцчить свойства документа по идентификатору документа и (возможно) группе свойства
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
			
			if ($valueType == 'valueDigit'){
				$v = $val = (int)$val;
			}else{
				$v = $val; makeSQLValue($v);
			}
			$db->dbValues->open("`$valueType` = $v");
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
//	Удалить из списка свойств, все системные свойства
function prop_filer(&$prop)
{
	foreach($prop as $name => &$val)
	{
		if ($name[0] != ':') continue;
		if (hasAccessRole('developer')) continue;
		unset($prop[$name]);
	}
}
function prop_value($db, $names, $data)
{
	$ret	= array();
	$names	= propSplit($names);
	foreach($names as &$name){
		makeSQLValue($name);
	}
	
	$sql	= array();
	$tableValues	= $db->dbValues->table;
	$sql[':join']["$tableValues v"]	= "v.`values_id` = `values_id`";
	$sql[':from'][]	= ' p';

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

		$db->dbValue->fields= "v.`$valueType` AS value";
		$db->dbValue->group	= "value";
		$db->dbValue->order	= "value";
		$sql[':where']	= "`prop_id` = $id";
		$db->dbValue->open($sql);
		while($d = $db->dbValue->next())
		{
			$n = $d['value'];
			if ($n) $ret[$name][$n] = $n;
		}
	}
	return $ret;
}
//	Подстчитать кол-во документов с заданными свойствами, вернуть массив название => количество
function prop_count($db, $names, &$search)
{
	$bSort = $names[0] == '!';
	if ($bSort) $names = substr($names, 1);
	//	Получить список свойств для обработки, разделяться дожные запятой без пробелов
	$names	= propSplit($names);
	//	Получить хеш значение для данных выборки
	$k	= "prop:count:".hashData($search).implode(',', $names);
	//	Проверить, еслть ли запрос в Memcache
	$ret= memGet($k);
	//	Если есть, то вернуть без обращения к БД
	if ($ret) return $ret;

//////////////
//	Получить список идентификаторов документов по выборке, в MySQL сильно ускоряет подсчет
	$ddb	= module('doc');
	$key	= $ddb->key();
	$table	= $ddb->table();
	if ($search['type']	== 'product'){
//		$search['price']	= '1-';
	}
	$tmpName	= 'tmp_'.md5(rand()+time());
	//	Получить SQL запрос
	$sql	= doc2sql($search);
	//	Выбрать идентификаторы, для ускорения выборки свойств
//	$tmpName= $ddb->selectKeys2table($key, $sql);
	$ids	= $ddb->selectKeys($key, $sql);
	//	Если документов нет, выернуть пустой массив
	if (!$ids) return array();
	$ddb->sql	=	'';
///////////////
	//	Возвращаемые значения
	$ret	= array();
	//	SQL запросы параметров
	$union	= array();

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	//	Сделать названия свойств текстовыми значениями SQL
	$n	= $names;
	foreach($n as &$name) makeSQLValue($name);
	//	Объеденить
	$n	= implode(',', $n);
	//	Сделать запрос и получить названия
	$db->open("`name` IN ($n)");
	//	Определить, насколько большой запрос получиться. Слишком большой запрос не лезет на некоторых серверах.
	$bLongQuery	= strlen($ids)*$db->rows() > 20*1024;
	//	Сформировать запросы по статистике для каждого свойства
	while($data = $db->next())
	{
		if ($bSort){
			$data['sort']	= array_search($data['name'], $names);
			$db->setData($data);
		}
		$sql			= array();
		//	Общий SQL запрос для всех видов, выборка по идентификатору документов
		if ($bLongQuery){
//			$sql[] = "find_in_set(`$key`, @ids)";
			$sql[] = "EXISTS (SELECT 1 FROM $tmpName AS idTable WHERE `doc_id` = idTable.id)";
		}else $sql[]	= "`$key` IN ($ids)";
		//	Посмотреть, есть ли кастомный обработчик запроса
		$queryName	= $data['queryName'];
		$ev			= array(&$db, &$sql, array());
		//	Выполнить запрос к кастомному обработчику
		if ($queryName) event("prop.query:$queryName", $ev);
		//	Если обработчик вернул SQL запрос, то пропускаем свойство
		if ($query = &$ev[2])
		{
			$union[]	= $ev[2];
		}else{
			//	Формируем стандартный SQL запрос
			$id		= $db->id();
			$name	= $data['name'];
			makeSQLValue($name);
			$sort	= $data['sort'];
			$sort2	= 0;

			$sql[':from'][]					= "p";
			$sql[':from']["prop_values_tbl"]= 'pv';
			$sql[]	= '`values_id`=pv.`values_id`';
			$sql[]	= "`prop_id`=$id";
			//	Группировать по идентификатору значения
			$db->dbValue->group		= "pv.`values_id`";
			//	Выводить поля name,value,sort,sort2,cnt - стандартные поля для будующего UNION запроса
			$db->dbValue->fields	= "$name AS name, pv.`$data[valueType]` AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
			//	Создать готовый SQL запрос
			$union[]	= $db->dbValue->makeSQL($sql);
		}
	}
	//	Если запрос ожидаеться большой, то занести данные в переменную SQL
	if ($bLongQuery){
//		$ddb->exec("SET @ids = '$ids'");
		$ids2	= implode('),(', explode(',', $ids));
		$q	= "CREATE TABLE `$tmpName` (`id` INT UNSIGNED NOT NULL, PRIMARY KEY (`id`)) ENGINE=MEMORY";
		$ddb->exec($q);
		$ddb->exec("INSERT INTO `$tmpName` VALUES ($ids2)");
	}
	//	Объеденить запросы, задать сортировку
	$union	= '(' . implode(') UNION (', $union) . ') ORDER BY `sort`, `sort2`, `name`, `value`';
	//	Выполнить общий запрос
	$ddb->exec($union);
	//	Записать полученные данные в массив
	while($data = $ddb->next()){
		$count	= $data['cnt'];
		if ($count) $ret["$data[name]"]["$data[value]"] = $count;
	}
	if ($bLongQuery){
		$ddb->exec("DROP TABLE `$tmpName`");
	}
	//	Записать в кеш
	memSet($k, $ret);
	//	Вернуть результат
	return $ret;
}
//	Получить список свойств
function prop_name($db, $group, $data)
{
	$sql		= array();
	$id			= $data['id'];
	if ($id){
		$ddb	= module('doc');
		$data	= $ddb->openID($id);
		$n		= $data['fields']['any']['searchProps'];
		if ($n && is_array($n)){
			foreach($n as &$val) makeSQLValue($val);
			$n		= implode(',', $n);
			$sql[]	= "`name` IN ($n)";
		};
	}
	
	$db->order	= '`name`';
	$group		= explode(',', $group);
	$ret		= array();
	$db->open($sql);
	while($data = $db->next()){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) continue;
		$ret[$data['name']] = $data;
	}
	return $ret;
}
//	Обновить кеш свойств
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
//		$ddb->exec($sql);
	}

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql	= "DELETE vs FROM $table2 AS vs WHERE `values_id` NOT IN (SELECT `values_id` FROM $table)";
	$db->exec($sql);
	
	$dbDoc		= module('doc');
	$docTable	= $dbDoc->table();
	$sql		= "DELETE v FROM $table AS v WHERE `doc_id` NOT IN (SELECT doc_id FROM $docTable)";
	$db->exec($sql);

	memClear();
	$a	= array();
	setCache('prop:nameCache', $a);
	unsetCache('prop:');
}
function prop_addQuery($db, $query, $queryName)
{
	$q	= getCacheValue('propertyQuery');
	if (!is_array($q)) $q = array();
	$q[$query] = $queryName;
	setCacheValue('propertyQuery', $q);
}
function prop_tools($db, $val, &$data)
{
	if (!hasAccessRole('admin,developer,writer')) return;
	$data['Все ствойства документов#ajax']	= getURL('property_all');
}
?>