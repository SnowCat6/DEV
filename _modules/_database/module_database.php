<?
 function module_db(&$val, &$data)
{
	$fn	= getFn("db_$val");
	if ($fn) return $fn($val, $data);
}
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
		$this->dbIni	= $dbIni;
		$bConnected		= $db->connected;
		if (!$bConnected && !moduleEx('db:connect', $this))
			return;
	
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
		//	Записать в лог
		module('message:sql:trace', "$time $sql");
		module('message:sql:error', $this->error());
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
	var $key;
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
				if ($this->id()==$id){
					$this->resetCache($id);
					$this->cache[$id]	= $this->data;
					return $this->data;
				}
				m('message:trace:error', "Document cache error $id");
			}
		}
		
		$key		= dbMakeField($this->key());
		$this->open("$key=$id");
		$data		= $this->next();
		
		if (isset($this->cache)) memSet($k, $data);
		return $data;
	}

	function delete($id){
		$fn	= getFn('database_delete');
		return $fn($this, $id);
	}
	function deleteByKey($key, $id){
		$fn	= getFn('database_deleteByKey');
		return $fn($this, $key, $id);
	}
	function sortByKey($sortField, $orderTable, $startIndex = 0)
	{
		$fn	= getFn('database_sortByKey');
		return $fn($this, $sortField, $orderTable, $startIndex);
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
		$fn	= getFn('database_makeRawSQL');
		return $fn($this, $where, $date);
	}
	
	function rowCompact()
	{
		dbDecode($this, $this->dbFields, $this->data);
		$this->setCacheValue();
		return $this->data;
	}
	function setCacheValue()
	{
		$data	= $this->data;
		$key	= $this->key;
		$id		= $data[$key];
		if (!$id) return;
		if (!isset($this->cache)) return;
		if (($this->fields != '' && !is_int(strpos($this->fields, '*')))) return;
		
		$this->resetCache($id);
		$this->cache[$id]	= $data;
		if (count($this->cache) <= 10) return;

		reset($this->cache);
		$k		= current($this->cache);
		$k		= $k[$key];
		$this->resetCache($k);
		$table	= $this->table();
		$count	= count($this->cache);
		m('message:cache:db', "$table($count) $key:+$id -$k");
	}
	function update($data, $doLastUpdate = true)
	{
		$db		= $this;
		$table	= $db->table();
		$key	= $db->key();
		$id		= makeIDS($data['id']);
		unset($data['id']);
	
		dbEncode($db, $db->dbFields, $data);
	
		if ($id){
			$k = dbMakeField($key);
			if (!$db->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else{
			$id = $db->insertRow($table, $data);
		}
		$db->resetCache($id);
		return $id?$db->data[$key]=$id:0;
	}
	//	util functions
	function setValue($id, $field, $val, $doLastUpdate = true){
		$data = array('id'=>$id, $field => $val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate = true){
		$data['id']=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, &$array, $bDelayed = false)
	{
		$fn	= getFn('database_insertRow');
		return $fn($this, $table, $array, $bDelayed);

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
	$v	= $db->dbLink->escape_string($val);
	return "'$v'";
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
function dbEncArray(&$db, &$val)
{
	if (is_null($val)){
		return 'NULL';
	}else{
		$v	= serialize($val);
		return dbEncString($db, $v);
	}
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
	foreach($data as $fieldName => &$val)
	{
		switch($dbFields[$fieldName])
		{
		case 'array':
			$val	= $bDecode?dbDecArray($db, $val):dbEncArray($db, $val);
			break;
		case 'datetime':
			$val	= $bDecode?dbDecDate($db, $val):dbEncDate($db, $val);
			break;
		case 'int':
		case 'tinyint':
			$val	= $bDecode?dbDecInt($db, $val):dbEncInt($db, $val);
			break;
		case 'float':
		case 'double':
			$val	= $bDecode?dbDecFloat($db, $val):dbEncFloat($db, $val);
			break;
		default:
			$val	= $bDecode?dbDecString($db, $val):dbEncString($db, $val);
			break;
		}
	}
}
?>