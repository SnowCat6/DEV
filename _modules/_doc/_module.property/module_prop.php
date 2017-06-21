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
function prop_selector($db, $name, &$data)
{
	$propertyData	= module("prop:getProperty:$name");
	$viewType		= $propertyData['viewType'];
	if (!$viewType) $viewType	= 'default';
	
	$fn	= getFn("prop_selector_$viewType");
	if ($fn) $fn($name, $data);
}
function propSplit($prop){
	return preg_split('#,(?!\s)#', $prop);
}
function propFormat($val, $data, $bUseFormat = true)
{
	if (!is_array($data)){
		$db		= module_prop(NULL, $data);
		$data	= propertyGetInt($db, $data);
	}
	if (!$data) return $val;
	
	if ($format = $data['format'])
	{
		if ($bUseFormat){
			$v = str_replace('%', "</span>$val<span>", "<span class=\"propFormat\"><span>$format</span></span>");
			return str_replace('<span></span>', '', $v);
		}else{
			return str_replace('%', $val, $format);
		}
	}
	return $bUseFormat?"<span class=\"propFormat\">$val</span>":$val;
}
function prop_get($db, $val, $data)
{
	$res	= prop_getEx($db, $val, $data);
	foreach($res as $name => &$property){
		$property	= $property['property'];
	}
	return $res;
}
//	Получить свойства документа по идентификатору документа и (возможно) группе свойства
function prop_getEx($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	if ($group)	$group	= explode(',', $group);
	else $group = array();
	
	$ddb	= module('doc');
	$data	= $ddb->openID($docID);
	if (!$data) return array();

	$res	= $data['property'];
	if (!is_array($res))
	{
		$res	= array();
		$sql	= array();
		$sql[]	= "v.`doc_id`=$docID";
		
		$table	= $db->dbValue->table();
		$table2	= $db->dbValues->table();
		$sql[':from'][]					= 'p';
		$sql[':join']["$table AS v"]	= 'v.`prop_id`=`prop_id`';
		$sql[':join']["$table2 AS vs"]	= 'vs.`values_id`=v.`values_id`';

		$db->fields	= "*, GROUP_CONCAT(DISTINCT vs.`valueText` SEPARATOR ', ') AS property";
		$db->group	= '`prop_id`';
		$db->order	= '`sort`';
		$db->open($sql);
		while($data = $db->next())
		{
			$res[$data['name']] = $data;
		}
		
		$ddb->setValue($docID, 'property', $res, false);
	}
	if (!$group) return $res;
	
	foreach($res as $name => $data){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) unset($res[$name]);
	}
	
	return $res;
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
		$name	= dbEncString($db, $name);
	}
	
	$sql			= array();
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

		switch($valueType){
		case 'valueDate':
			$db->dbValue->fields= "DATE_FORMAT(v.`$valueType`,'%d.%m.%Y') AS value";
			break;
		default:
			$db->dbValue->fields= "v.`$valueType` AS value";
		}
		
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

	$names	= trim($names);
	//	Получить список свойств для обработки, разделяться дожные запятой без пробелов
	$names	= propSplit($names);
	//	Получить хеш значение для данных выборки
	$k	= "prop:count:".hashData($search).implode(',', $names).($bSort?':+sort':'');
	//	Проверить, еслть ли запрос в Memcache
	$ret= getCache($k, 'file');
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
	$tmpName= 'tmp_'.md5(rand()+time());
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
	foreach($n as &$name) $name = dbEncString($db, $name);
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
			$sql[] 	= "EXISTS (SELECT 1 FROM $tmpName AS idTable WHERE `doc_id` = idTable.id)";
		}else $sql[]= "`$key` IN ($ids)";
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
			$name	= dbEncString($db, $name);
			$sort	= $data['sort'];
			$sort2	= 0;

			$sql[':from'][]			= "p";
			$sql[':from'][$table2]	= 'pv';
			$sql[]	= '`values_id`=pv.`values_id`';
			$sql[]	= "`prop_id`=$id";
			//	Группировать по идентификатору значения
			$db->dbValue->group		= "pv.`values_id`";
			//	Выводить поля name,value,sort,sort2,cnt - стандартные поля для будующего UNION запроса
			$fieldName			= intPropDec($db, $data['valueType'], "pv.`$data[valueType]`");
			$db->dbValue->fields= "$name AS name, $fieldName AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
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
	setCache($k, $ret, 'file');
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
			foreach($n as &$val) $val = dbEncString($db, $val);
			$n		= implode(',', $n);
			$sql[]	= "`name` IN ($n)";
		};
	}
	$names	= $data['name'];
	if ($names){
		$names	= makeIDS($names);
		$sql[]	= "`name` IN ($names)";
	}

//	$cache		= getCache('prop:nameCache', 'ram');

	$db->order	= '`sort`,`name`';
	$group		= $group?explode(',', $group):array();
	$ret		= array();
	$db->open($sql);
	while($data = $db->next())
	{
		setCache("prop:nameCache:$data[name]", $data, 'ram');
/*
		$propertyName			= $data['name'];
		if (!$cache[$propertyName])
		{
			$cache[$propertyName]	= $data;
//			setCache('prop:nameCache', $cache, 'ram');
		}
*/		
		if ($group){
			$g = explode(',', $data['group']);
			if (!array_intersect($group, $g)) continue;
		}
		$ret[$data['name']] = $data;
	}

	return $ret;
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
//	Получить данные свойства по имени
function prop_getProperty(&$db, $propertyName, &$data){
	return propertyGetInt($db, $propertyName);
}
function propertyGetInt(&$db, $propertyName)
{
//	$cache	= getCache('prop:nameCache', 'ram');
//	$data	= $cache[$propertyName];
	$data	= getCache("prop:nameCache:$propertyName", 'ram');
	if ($data)
	{
		$db->data	= $data;
	}else{	//	Заполнить кеш
		$name	= $propertyName;
		$name	= dbEncString($db, $name);
		
		$db->open("`name`=$name");
		$data	= $db->next();
		setCache("prop:nameCache:$propertyName", $data, 'ram');
		
//		$cache[$propertyName]	= $data;
//		setCache('prop:nameCache', $cache, 'ram');
	}
	return $data;
}
//	Кодирует значение в зависимости от типа данных для подстановки в SQL
function intPropEnc(&$db, $valueType, $value)
{
	switch($valueType)
	{
	case 'valueDate':
		if (preg_match('#(\d{1,2})\.(\d{1,2})\.(\d{4})#', $value, $v)){
			list(, $d, $m, $y) = $v;
			$value	= mktime(0, 0, 0, $m, $d, $y);
		}else $value = NULL;

		$value	= dbEncDate($db, $value);
		break;
	case 'valueDigit':
		$value	= (int)$value;
		break;
	default:
		$value = "$value";
		$value	= dbEncString($db, $value);
	}
	return $value;
}
//	Кодирует вывод из базы данных определенного поля (форматрирует вывод SQL)
function intPropDec(&$db, $valueType, $fieldName)
{
	switch($valueType){
	case 'valueDate':
		$fieldName	= "DATE_FORMAT($fieldName,'%d.%m.%Y')";
		break;
	case 'valueDigit':
		break;
	default:
	}
	return $fieldName;
}
?>