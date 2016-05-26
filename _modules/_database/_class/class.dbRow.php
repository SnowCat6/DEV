<?
//	Конфигурация базы данных
class dbConfig
{
	var		$ini;		//	Конфигурация базы данных, логин, пароль
	//	Получить конфигурацию базы данных, логин пароль и прочее.
	function getConfig()
	{
		if ($this->ini) return $this->ini;
		
		$this->ini	= getCacheValue('dbIni');
		if ($this->ini) return $this->ini;

		return dbIni::get();
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
		return $this->connectEx($this->getConfig(), $bCreateDatabase);
	}
	//	Соеденить с базой данных по передонному конфигурационному фвйлу
	function connectEx($dbIni, $bCreateDatabase = false)
	{
		$this->dbIni	= $dbIni;
		if (!$this->connected){
			if (!dbIni::connect($this)) return;
		}
		
		//	Сконфигурировать базу
		$dbName	= $dbIni['db'];
		if (!$dbName) return false;
		
		//	Создать базы данных
		if (!$bCreateDatabase || $this->dbCreated)
		{
			if (!$this->dbCreated)
				$this->dbCreated = $this->dbSelect($dbName);
			return true;
		}

		$this->dbCreated = $this->dbSelect($dbName);
		if (!$this->dbCreated)
		{
			$this->dbExec("CREATE DATABASE `$dbName`");
			if ($this->error())
			{
				module('message:sql:error', $this->error());
				module('message:error', 'Ошибка открытия базы данных. ' . $this->error());
				return false;
			}
			$this->dbCreated = true;
		}

		return $this->dbSelect($dbName);
	}
	function dbSelect($db)			{
		module('message:sql:trace', "USE DATABASE `$db`");
		return $this->dbLink->select_db($db);
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
		if (!$dbLink) $dbLink = config::get('dbLink');
		if (!$dbLink){
			$dbLink	= new dbConnect();
			$dbLink->create();
			config::set('dbLink', $dbLink);
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
			$table		= $this->table;
			$this->cache= "dbCache:$table";
		}else{
			$this->cache	= '';
		}
	}
	function setData($data){
		$this->fields	= '*';
		$this->data		= $data;
		$this->setCacheValue();
	}
	function setCacheData($id, $data){
		$this->setData($data);
	}
	function resetCache($id)
	{
		$cacheName	= $this->cache;
		if (!$cacheName) return;
		
		$cache		= config::get($cacheName);
		$cache[$id] = NULL;
		unset($cache[$id]);
		config::set($cacheName, NULL);
	}
	function clearCache($id = NULL)
	{
		$cacheName	= $this->cache;
		if (!$cacheName) return;

		config::set($cacheName, NULL);
		memClear($cacheName);
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

		$cacheName	= $this->cache;
		$k			="$cacheName:$id";
		if ($cacheName)
		{
			$this->data	= memGet($k);
			if ($this->data) return $this->data;
			
			$cache		= config::get($cacheName);			
			$this->data	= $cache[$id];
			if ($this->data) return $this->data;
		}
		
		$key		= dbMakeField($this->key());
		$this->open("$key = $id");
		$data		= $this->next();
		
		if ($cacheName){
			memSet($k, $data);
			$cache		= config::get($cacheName);
			$cache[$id]	= $data;
			config::set($cacheName, $cache);
		}
		return $data;
	}

	function delete($id){
		return dbWrite::delete($this, $id);
	}
	function deleteByKey($key, $id){
		return dbWrite::deleteByKey($this, $key, $id);
	}
	function sortByKey($sortField, $orderTable, $startIndex = 0)
	{
		return dbWrite::sortByKey($this, $sortField, $orderTable, $startIndex);
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
	function nextItem()
	{
		$data	= $this->next();
		return $data?new dbItem($this, $data):NULL;
	}
	function next()
	{ 
		if ($this->max && $this->maxCount >= $this->max){
			$this->data = NULL;
			return NULL;
		}
		$this->maxCount++;
		$this->ndx++;
		$this->data = $this->dbLink->dbResult($this->res);

		dbDecode($this, $this->dbFields, $this->data);
		$this->setCacheValue();

		return $this->data;
	}
	function rowCompact()
	{
		return $this->data;
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
		return dbSQL::makeRaw($this, $where, $date);
	}
	
	function setCacheValue()
	{
		if (($this->fields != '' && !is_int(strpos($this->fields, '*')))) return;
		
		$cacheName	= $this->cache;
		if (!$cacheName) return;

		$data	= $this->data;
		$key	= $this->key;
		$id		= $data[$key];
		if (!$id) return;
		
		$cache		= config::get($cacheName);
		$k			="$cacheName:$id";
		memset($k, $data);
		
		if (count($cache) > 10)
		{
			reset($cache);
			$k		= current($cache);
			$k		= $k[$key];
			unset($cache[$k]);
			$cache[$id]	= $data;
			
			$table	= $this->table();
			$count	= count($cache);
			m('message:cache:db', "$table($count) $key:+$id -$k");
		}else{
			$cache[$id]	= $data;
		}
		config::set($cacheName, $cache);
	}
	function update($data, $doLastUpdate = false)
	{
		$db		= $this;
		$table	= $db->table();
		$key	= $db->key();
		$id		= makeIDS($data['id']);
		unset($data['id']);
		
		if ($doLastUpdate) $data['lastUpdate']	= time();
	
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
	function setValue($id, $field, $val, $doLastUpdate = false){
		$data = array('id'=>$id, $field => $val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate = false){
		$data['id']	=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, &$array, $bDelayed = false)
	{
		return dbWrite::insertRow($this, $table, $array, $bDelayed);
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
	function folder($id = 0)
	{
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
?>