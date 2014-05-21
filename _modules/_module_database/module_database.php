<?
//	Конфигурация базы данных
class dbConfig
{
	var		$ini;		//	Конфигурация базы данных, логин, пароль
	//	Получить конфигурацию базы данных, логин пароль и прочее.
	function getConfig()
	{
		return getCacheValue('dbIni');
	}
	//	Получить префикс таблиц для уникального наименования сайта в базе данных
	function dbTablePrefix()
	{
		$dbIni	= $this->getConfig();
		return $dbIni['prefix'];
	}
	function dbName()
	{
		$dbIni	= $this->getConfig();
		return $dbIni['db'];
	}
	//	Получить полное название таблицы прибавив префикс
	function dbTableName($name){
		$prefix = $this->dbTablePrefix();
		return "$prefix$name";
	}
};

//	Основной класс базовых функций работы с БД
class dbConnect extends dbConfig
{
	var		$dbLink;	//	Объект MySQLi
	var		$connected;	//	База данных подключена
	var		$dbCreated;	//	Создание базы данных было (выполнение комманды создания БД)
	//	Созать объект MySQLi или использовать внешний
	function create($dbLink = NULL){
		return $this->dbLink 	= $dbLink?$dbLink:new MySQLi();
	}
	//	Соеденить с базой данных, если надо - то создать базу данных
	function connect($bCreateDatabase = false)
	{
		if ($this->connected) return;
		return $this->connectEx($this->getConfig(), $bCreateDatabase);
	}
	//	Соеденить с базой данных по передонному конфигурационному фвйлу
	function connectEx($dbIni, $bCreateDatabase = false)
	{
		$bConnected		= $this->connected;
		if (!$bConnected)
		{
			$dbhost	= $dbIni['host'];
			$dbuser	= $dbIni['login'];
			$dbpass	= $dbIni['passw'];
			//	Если нет dbHost connect вылетает с ошибкой
			if (!$dbhost) return;
		
			$timeStart	= getmicrotime();
			$this->dbLink->connect($dbhost, $dbuser, $dbpass);
			$time 		= round(getmicrotime() - $timeStart, 4);
			//	Записать в лог, если это не восстановление бекапа
			if (!defined('restoreProcess')){
				module('message:sql:trace', "$time CONNECT to $dbhost");
				module('message:sql:error', $this->dbLink->error);
			}
			//	Если ошибка, то зафиксируем ее
			if ($this->dbLink->error){
				module('message:sql:error', $this->dbLink->error);
				module('message:error', 'Ошибка открытия базы данных.');
				return;
			}
			//	Все соедедено, продлжить
			$this->connected	= true;
			$this->dbExec("SET NAMES UTF8");
		}

		$db	= $this->dbName();		
		//	Создать базы данных
		if ($db && $bCreateDatabase && !$this->dbCreated){
			$this->dbCreated = true;
			$this->dbExec("CREATE DATABASE `$db`");
		}
		if ($bConnected) return true;
		//	Сконфигурировать базу
		$this->dbSelect($db);
		
		return true;
	}
	//	Выполнить SQL запрос
	function dbExec($sql, $rows = 0, $from = 0, &$dbLink = NULL)
	{
		//	Соеденить
		$this->connect();
		if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";
		//	Выполнить
		$timeStart	= getmicrotime();
		$res		= $this->dbLink->query($rows?"$sql LIMIT $from, $rows":$sql);
		$time 		= round(getmicrotime() - $timeStart, 4);
		//	Записать в лог, если не восстановление архива
		if (!defined('restoreProcess')){
			module('message:sql:trace', "$time $sql");
			module('message:sql:error', $this->error());
		}
		//	Вернуть результат
		return $res;
	}
	function dbSelect($db)			{
		module('message:sql:trace', "USE DATABASE `$db`");
		return $this->dbLink->select_db($db);
	}
	function dbRows($id)			{ return $this->dbLink->affected_rows; }
	function dbResult($id)			{ return $id?$id->fetch_array(MYSQLI_ASSOC):NULL;}
	function dbRowTo($id, $row)		{ return $id?$id->data_seek($row):NULL; }
	function error()				{ return $this->dbLink->error; }
	function dbExecIns($sql, $rows = 0){
		$this->dbExec($sql, $rows, 0);
		return $this->dbLink->insert_id;
	}
	//	Выполнить несколько запросов
	function dbExecQuery($sql){ 
		$err= array();
		$q	= explode(";\r\n", $sql);
		while(list(,$sql)=each($q)){
			if (!$sql) continue;
			if ($this->dbExec($sql, 0, 0)) continue;
			$e 		= $this->dbLink->error;
			$err[] 	= $e;
		}
		return $err;
	}
	//	Маскировать строку
	function escape_string($val){
		$this->connect();
		$val = $this->dbLink->escape_string($val);
		return $val;
	}
};
//	Соновной класс для работы с БД
class dbRow
{
	var $dbLink;
	var $dbFields;
	var $ley;
	var $table;
	var $max;
	var $rows;
//	main functions
	function dbRow($table = '', $key = '', $dbLink = NULL)
	{
		$lnk	= &$GLOBALS['_CONFIG']['dbLink'];
		if (!$dbLink) $dbLink = $lnk;
		if (!$dbLink){
			$dbLink	= new dbConnect();
			$dbLink->create();
			$lnk	= $dbLink;
		}
		$this->dbLink	= $dbLink;
		$this->table	= $this->dbLink->dbTableName($table);;
		$this->key 		= $key;
		$this->max		= 0;
		$this->rows		= 0;
		
		$dbFields		= getCacheValue('dbFields');
		$dbFields		= $dbFields[$this->table];
		$this->dbFields	= is_array($dbFields)?$dbFields:array();
	}
	function getConfig(){
		return $this->dbLink->getConfig($val);
	}
	function dbName(){
		return $this->dbLink->dbName();
	}
	function dbTablePrefix(){
		return $this->dbLink->dbTablePrefix();
	}
	function escape_string($val){
		return $this->dbLink->escape_string($val);
	}
	function error(){
		return $this->dbLink->error();
	}
	function reset()		{
		$this->order = $this->group = $this->fields = '';
	}
	function setCache($bSetCache = true)
	{
		if ($bSetCache){
			if (isset($this->cache)) return;
			$cache	= &$GLOBALS['_CONFIG'];
			$cache	= &$cache['dbCache'];
			$cache	= &$cache[$this->table];
			if (!isset($cache)) $cache = array();
			$this->cache = &$cache;
		}else{
			$this->cache	= NULL;
			unset($this->cache);
		}
	}
	function setData(&$data){
		$this->fields	= '*';
		$this->data		= $data;
		$this->setCacheValue();
	}
	function setCacheData($id, &$data){
		$this->setData($data);
	}
	function resetCache($id){
		if (!isset($this->cache)) return;
		$this->cache[$id] = NULL;
		unset($this->cache[$id]);
	}
	function clearCache($id = NULL)
	{
		if (!isset($this->cache)) return;

		if ($id) $this->resetCache($id);
		else $this->cache = array();

		memClear($this->table());
	}
	function open($where='', $max=0, $from=0, $date=0)
	{
		return $this->exec($this->makeSQL($where, $date), $max, $from);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		if ($ids){
			$key 	= dbMakeField($this->key());
			return $this->open("$key IN ($ids)");
		}
		return $this->open('false');
	}
	function openID($id)
	{
		$id		= (int)$id;
		if (!$id) return;

		if (isset($this->cache))
		{
			$k			= $this->table().":$id";
			$this->data	= memGet($k);
			if ($this->data) return $this->data;
			
			$this->data	= $this->cache[$id];
			if ($this->data){
				if ($this->id()==$id)  return $this->data;
				m('message:trace:error', "Document cache error $id");
//				print_r($this->cache);
			}
		}
		
		$key	= dbMakeField($this->key());
		$this->open("$key = $id");
		$data	= $this->next();
		
		if (isset($this->cache)){
			if (memSet($k, $data)) return $data;
			$this->cache[$id]	= $data;
		}
		return $data;
	}

	function delete($id){
		$table	=	$this->table();
		$key 	=	$this->key();
		$id		=	makeIDS($id);
		$key 	=	dbMakeField($key);
		$table	=	dbMakeField($table);
		$this->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	function deleteByKey($key, $id){
		$key	= dbMakeField($key);
		$table	= $this->table();
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $this->exec($sql);
	}
	function sortByKey($sortField, $orderTable, $startIndex = 0)
	{
		if (!is_array($orderTable)) return;
		
		$sortField	= dbMakeField($sortField);
		$key		= $this->key();
		$table		= $this->table();

		$nStep	= (int)$startIndex;
		$sql	= '';
		foreach($orderTable as $id){
			$nStep += 1;
			$id	= dbEncString($this, $id);
			$this->exec("UPDATE $table SET $sortField = $nStep WHERE $key=$id");
		}
	}
	function selectKeys($key, $sql = '', $bStringResult = true)
	{
		$ids			= array();
		$key			= dbMakeField($key);
		$this->fields	= "$key AS id";
		$sql[]			= $this->sql;
		$res			= $this->dbLink->dbExec($this->makeSQL($sql), 0, 0);
		while($data = $this->dbLink->dbResult($res)) $ids[] = $data['id'];
		return $bStringResult?implode(',', $ids):$ids;
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function execSQL($sql)	{ return $this->dbLink->dbExec($sql, 0, 0); }
	function exec($sql, $max = 0, $from = 0){
		$this->maxCount	= $this->ndx = 0;
		$this->res		= $this->dbLink->dbExec($sql, $max, $from);
		$this->rows		= $this->dbLink->dbRows($this->res);
		return $this->res;
	}
	function dbResult(){
		return $this->dbLink->dbResult($this->res);
	}
	function next(){ 
		if ($this->max && $this->maxCount >= $this->max){
			$this->data = NULL;
			return NULL;
		}
		$this->maxCount++;
		$this->ndx++;
		$this->data = $this->dbLink->dbResult($this->res);
		return $this->rowCompact();
	}
	function rows()			{ return $this->rows; }
	function seek($row)		{ $this->dbLink->dbRowTo($this->res, $row); }
	function id()			{ return $this->data[$this->key()]; }
	function makeSQL($where, $date = 0)	{
		$sql = $this->makeRawSQL($where, $date);
		$sql['from']	= "FROM $sql[from]";
		return implode(' ', $sql);
	}
	function makeRawSQL($where, $date = 0)
	{
		if (!is_array($where)) $where = $where?array($where):array();
		
		$join		= '';
		$thisAlias	= '';
		$table		= dbMakeField($this->table());
		$group		= $this->group;

		if ($this->fields) $fields = $this->fields;
		else $fields = '*';

		if ($val = $where[':from'])
		{
			unset($where[':from']);

			$t = array();
			foreach($val as $name => $alias)
			{
				if (!$alias) continue;
				if (is_int($name)){
					$t[]		= "$table AS $alias";
					$thisAlias	= $alias;
				}else{
					$name		= $this->dbLink->dbTableName($name);
					$t[]		= "$name AS $alias";
				}
			}
			$table = implode(', ', $t);
		}
		if ($val = $where[':fields']){
			unset($where[':fields']);
			$fields = $val;
		}
		if ($val = $where[':group']){
			unset($where[':group']);
			$group = $val;
		}
		if ($val = $where[':join'])
		{
			unset($where[':join']);
			foreach($val as $joinTable => $joinWhere){
				$join  .= "INNER JOIN $joinTable ON $joinWhere";
			}
		}
		if ($this->sql)
			$where[] .= $this->sql;
			
		if ($date){
			$date		= dbEncDate($this, $date);
			$where[]	= "lastUpdate > $date";
		}
		
		$where = implode(' AND ', $where);
		
		if ($where) $where = "WHERE $where";
		if ($order = $this->order) $order = "ORDER BY $order";
		if ($group)	$group = "GROUP BY $group";
		
		//	Заменить названия полей на название с алиасом
		if ($thisAlias)
		{
			$fields	= preg_replace('#(\s|^)\*#',	"\\1$thisAlias.*",	$fields);
			
			$r = '#([\s=(]|^)(`[^`]*`)#';
			$fields	= preg_replace($r, "\\1$thisAlias.\\2" ,$fields);
			$join	= preg_replace($r, "\\1$thisAlias.\\2", $join);
			$where	= preg_replace($r, "\\1$thisAlias.\\2", $where);
			$group	= preg_replace($r, "\\1$thisAlias.\\2", $group);
			$order	= preg_replace($r, "\\1$thisAlias.\\2", $order);
		}

		$sql 			= array();
		$sql['action']	= 'SELECT';
		$sql['fields']	= $fields;
		$sql['from']	= $table;
		$sql['join']	= $join;
		$sql['where']	= $where;
		$sql['group']	= $group;
		$sql['order']	= $order;
		return $sql;
	}
	
	function rowCompact()
	{
		dbDecode($this, $this->dbFields, $this->data);
		$this->setCacheValue(true);
		return $this->data;
	}
	function setCacheValue($bRemoveTop = false)
	{
		$id	= $this->id();
		if (!$id) return;
		
		if (!isset($this->cache) ||
		 ($this->fields != '' && !is_int(strpos($this->fields, '*')))) return;
		 
		if (count($this->cache) > 10){
			if ($bRemoveTop){
				$k	= end($this->cache);
			}else{
				reset($this->cache);
				$k	= current($this->cache);
			}
			$key	= $this->key;
			$k		= $k[$key];
			$this->resetCache($k);
			$table	= $this->table();
			$count	= count($this->cache);
			m('message:trace', "db cache $table clear, total $count:$k");
		}
		$this->cache[$id] = $this->data;
	}
	function update($data, $doLastUpdate = true)
	{
		$table	= $this->table();
		$key	= $this->key();
		$id		= makeIDS($data['id']);
		unset($data['id']);

//		if ($doLastUpdate) $data['lastUpdate']	= time();
		dbEncode($this, $this->dbFields, $data);

		if ($id){
			$k = dbMakeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else{
			$id = $this->insertRow($table, $data);
		}
		$this->resetCache($id);
		return $id?$this->data[$key]=$id:0;
	}
	//	util functions
	function setValue($id, $field, $val, $doLastUpdate = true){
		$data = array('id'=>$id, $field=>$val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate = true){
		$data['id']=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, &$array, $bDelayed = false)
	{
		reset($array);
		$table = dbMakeField($table);
		$fields=''; $comma=''; $values='';
		foreach($array as $field => $value)
		{
			$field	= dbMakeField($field);
			$fields	.= "$comma$field";
			$values	.= "$comma$value";
			$comma	= ',';
		}
		
		if ($bDelayed) $res = $this->dbLink->dbExec("INSERT DELAYED INTO $table ($fields) VALUES ($values)", 0, 0);
		else $res =  $this->dbLink->dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0);

		unset($table);
		unset($fields);
		unset($values);

		return $res;
	}
	function updateRow($table, &$array, $sql)
	{
		reset($array);
		$table	= dbMakeField($table);
		$command= ''; $comma= 'SET ';
		while(list($field, $value)=each($array)){
			$field	=dbMakeField($field);
			$command.="$comma$field=$value";
			$comma	= ',';
		}
		return $this->execSQL("UPDATE $table $command $sql");
	}
	function folder($id = 0){
		if (!$id) $id = $this->id();
		if ($id){
			$fields	= $this->data['fields'];
			$path	= $fields['filepath'];
			if ($path) return $this->images.'/'.$path;
		}
		$userID = function_exists('userID')?userID():0;
		return $this->images.'/'.($id?$id:"new$userID");
	}
	function url($id = 0)		{ return $this->url.($id?$id:$this->id()); }
};

function makeIDS($id, $separator = ',')
{
	$db	= new dbRow();
	if (!is_array($id)) $id = explode($separator, $id);
	foreach($id as $ndx => &$val)
	{
		$val = trim($val);
		if (preg_match('#^\d+$#', $val)) $val = (int)$val;
		else{
			if ($val) $val = dbEncString($db, $val);
			else unset($id[$ndx]);
		}
	}
	return implode($separator, $id);
}

function makeDateStamp($val){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $val, $v)){
		list(,$d,$m,$y) = $v;
		return mktime(0, 0, 0, $m, $d, $y);
	}else
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return mktime($h, $i, 0, $m, $d, $y);
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return mktime($h, $i, $s, $m, $d, $y);
	}
	return 0;
}
function dateStamp($val){
	if (!$val) return;
	return date('d.m.Y H:i', $val);
}
/**************************************/
function dbMakeField($val)
{
	return "`$val`";
}
/**************************************/
function dbEncString(&$db, &$val){
	$val	= $db->dbLink->escape_string($val);
	return "'$val'";
}
function dbDecString(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncInt(&$db, &$val){
	return (int)$val;
}
function dbDecInt(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncFloat(&$db, &$val){
	return (float)$val;
}
function dbDecFloat(&$db, &$val){
	return $val;
}
/**************************************/
function dbEncArray(&$db, &$val){
	$val	= serialize($val);
	return dbEncString($db, $val);
}
function dbDecArray(&$db, &$val){
	return unserialize($val);
}
/**************************************/
function dbEncDate(&$db, $val){
	if (!$val) return 'NULL';
	$val	= date('Y-m-d H:i:s', (float)$val);
	return	"DATE_ADD('$val', INTERVAL 0 DAY)";
}
function dbDecDate(&$db, $val){
	if (!preg_match('#(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})#', $val, $m))
		return NULL;

	$d = mktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
	if ($d < 0) return NULL;

	return $d;
}
/**************************************/
function dbEncode(&$db, &$dbFields, &$data)
{
	dbCoDec($db, $dbFields, $data, false);
}
function dbDecode(&$db, &$dbFields, &$data)
{
	dbCoDec($db, $dbFields, $data, true);
}
/**************************************/
function dbCoDec(&$db, &$dbFields, &$data, $bDecode)
{
	foreach($dbFields as $fieldType => &$fields)
	{
		switch($fieldType){
		case 'array':
			foreach($fields as $fieldName => $def){
				if (!array_key_exists($fieldName, $data)) continue;
				$val	= &$data[$fieldName];
				$val	= $bDecode?dbDecArray($db, $val):dbEncArray($db, $val);
			}
			break;
		case 'datetime':
			foreach($fields as $fieldName => $def){
				if (!array_key_exists($fieldName, $data)) continue;
				$val	= &$data[$fieldName];
				$val	= $bDecode?dbDecDate($db, $val):dbEncDate($db, $val);
			}
			break;
		case 'int':
		case 'tinyint':
			foreach($fields as $fieldName => $def){
				if (!array_key_exists($fieldName, $data)) continue;
				$val	= &$data[$fieldName];
				$val	= $bDecode?dbDecInt($db, $val):dbEncInt($db, $val);
			}
			break;
		case 'float':
		case 'double':
			foreach($fields as $fieldName => $def){
				if (!array_key_exists($fieldName, $data)) continue;
				$val	= &$data[$fieldName];
				$val	= $bDecode?dbDecFloat($db, $val):dbEncFloat($db, $val);
			}
			break;
		default:
			foreach($fields as $fieldName => $def){
				if (!isset($data[$fieldName])) continue;
				$val	= &$data[$fieldName];
				$val	= $bDecode?dbDecString($db, $val):dbEncString($db, $val);
			}
		}
	}
}
?>