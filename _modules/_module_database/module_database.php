<?
class dbConfig
{
	var		$dbLink;
	var		$ini;
	var		$connected;
	var		$dbCreated;
	function create($dbLink = NULL){
		return $this->dbLink 	= $dbLink?$dbLink:new MySQLi();
	}
	function getConfig()
	{
		if (isset($this->ini)) return $this->ini;
		//	Смотрим локальные настройки базы данных
		$ini		= getCacheValue('ini');
		$dbIni		= $ini[':db'];
		//	Если их нет, пробуем глобальные
		if (!is_array($dbIni)){
			//	Получим глобальные правила
			$globalDb	= $ini[':globalSiteDatabase'];
			if (!is_array($globalDb)){
				$ini		= getGlobalCacheValue('ini');
				//	Получим глобальные правила
				$globalDb	= $ini[':globalSiteDatabase'];
				if (!is_array($globalDb)) $globalDb = array();
			}
			//	Пройдемся по правилам
			foreach($globalDb as $rule => $dbKey){
				if (!preg_match("#$rule#i", $_SERVER['HTTP_HOST'])) continue;
				//	Если правило подходит, возмем значение из нового ключа
				$dbIni	= $ini[$dbKey];
				break;
			}
			//	Если настроек не найдено, пробуем стандартные
			if (!is_array($dbIni))
				$dbIni = $ini[':db'];
		}
		return $this->ini = $dbIni;
	}
	function dbConnect($bCreateDatabase = false)
	{
		return $this->dbConnectEx($this->getConfig(), $bCreateDatabase);
	}
	function dbConnectEx($dbIni, $bCreateDatabase = false)
	{
		if (!$this->connected){
			$ini		= getGlobalCacheValue('ini');
			$pConnect	= $ini[':']['mySQLpconnect'];
			
			$dbhost	= $dbIni['host'];
			$dbuser	= $dbIni['login'];
			$dbpass	= $dbIni['passw'];
			$db		= $dbIni['db'];
		
			$timeStart	= getmicrotime();
			$cnn		= NULL;
			if ($pConnect)	$cnn = $this->dbLink->connect($dbhost, $dbuser, $dbpass);
			if (!$cnn)		$cnn = $this->dbLink->connect($dbhost, $dbuser, $dbpass);
			$time 		= round(getmicrotime() - $timeStart, 4);

			if (!defined('restoreProcess')){
				module('message:sql:trace', "$time CONNECT to $dbhost");
				module('message:sql:error', $this->dbLink->error);
			}
			if ($this->dbLink->error){
				module('message:sql:error', $this->dbLink->error);
				module('message:error', 'Ошибка открытия базы данных.');
				return;
			}
		}
		if ($bCreateDatabase && $this->dbCreated){
			$this->dbExec("CREATE DATABASE `$db`");
			$this->dbCreated = true;
		}
		if ($this->connected) return;
	
		$this->dbExec("SET NAMES UTF8");
		$this->dbSelect($db);
		$this->connected = true;
		
		return true;
	}
	function dbTablePrefix()
	{
		$dbConfig	= $this->getConfig();
		$prefix		= $dbConfig['prefix'];
		
		$constName	= "tablePrefix_$prefix";
		if (defined($constName)) return constant($constName);

		$url		= preg_replace('#[^\d\w]+#', '_', getSiteURL());
		if (!$prefix) $p = $url.'_';
		else $p = "$url_$prefix".'_';
		define($constName, $p);
		return $p;
	}
	function dbTableName($name){
		$prefix = $this->dbTablePrefix();
		return "$prefix$name";
	}
	function dbExec($sql, $rows=0, $from=0, &$dbLink = NULL)
	{
		if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";
	
		$timeStart	= getmicrotime();
		$res		= $this->dbLink->query($rows?"$sql LIMIT $from, $rows":$sql);
		$time 		= round(getmicrotime() - $timeStart, 4);
	
		if (!defined('restoreProcess')){
			module('message:sql:trace', "$time $sql");
			module('message:sql:error', $this->dbLink->Error);
		}
	
		return $res;
	}
	function dbSelect($db)			{ return $this->dbLink->select_db($db); }
	function dbRows($id)			{ return $this->dbLink->affected_rows; }
	function dbResult($id)			{ return $id?$id->fetch_array(MYSQLI_ASSOC):NULL;}
	function dbRowTo($id, $row)		{ return $id?$id->data_seek($row):NULL; }
	function dbExecIns($sql, $rows = 0){
		$this->dbExec($sql, $rows, 0);
		return $this->dbLink->insert_id;
	}
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
	function escape_string($val){
		$val = $this->dbLink->escape_string($val);
		return $val;
	}
};

class dbRow
{
	var $dbLink;
//	main functions
	function dbRow($table = '', $key = '', $dbLink = 0)
	{
		if (!$dbLink) $dbLink = $GLOBALS['_CONFIG']['dbLink'];
		if (!$dbLink){
			$dbLink	= new dbConfig();
			$dbLink->create();
			$dbLink->dbConnect();
			$GLOBALS['_CONFIG']['dbLink']	= $dbLink;
		}
		$this->dbLink	= $dbLink;
		$this->table	= $this->dbLink->dbTableName($table);;
		$this->max		= 0;
		$this->key 		= $key;
	}
	function error(){
		return $this->dbLink->Error;
	}
	function reset()		{
		$this->order = $this->group = $this->fields = '';
	}
	function setCache(){
		if (!isset($this->cache)){
			$cache	= &$GLOBALS['_CONFIG'];
			$cache	= &$cache['dbCache'];
			$cache	= &$cache[$this->table];
			if (!isset($cache)) $cache = array();
			$this->cache = &$cache;
		}
	}
	function setCacheData($id, &$data){
		if (isset($this->cache)) $this->cache[$id] = $data;
	}
	function resetCache($id){
		if (isset($this->cache)) $this->cache[$id] = NULL;
	}
	function clearCache($id = NULL){
		if (isset($this->cache)){
			if ($id) $this->cache[$id] = NULL;
			else $this->cache = array();
		}
	}
	function open($where='', $max=0, $from=0, $date=0)
	{
		return $this->exec($this->makeSQL($where, $date), $max, $from);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		if ($ids){
			$key 	= makeField($this->key());
			return $this->open("$key IN ($ids)");
		}
		return $this->open('false');
	}
	function openID($id)
	{
		$id		= (int)$id;
		if (isset($this->cache)){
			$data = $this->cache[$id];
			if (isset($data)) return $data;
		}
		
		$key	= makeField($this->key());
		$this->open("$key = $id");
		$data	= $this->next();
		
		if (isset($this->cache)) $this->cache[$id] = $data;
		return $data;
	}

	function delete($id){
		$table	=	$this->table();
		$key 	=	$this->key();
		$id		=	makeIDS($id);
		$key 	=	makeField($key);
		$table	=	makeField($table);
		$this->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	function deleteByKey($key, $id){
		$key	= makeField($key);
		$table	= $this->table();
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $this->exec($sql);
	}
	function sortByKey($sortField, &$orderTable, $startIndex = 0)
	{
		if (!is_array($orderTable)) return;
		
		$sortField	= makeField($sortField);
		$key		= $this->key();
		$table		= $this->table();

		$nStep	= (int)$startIndex;
		$sql	= '';
		foreach($orderTable as $id){
			$nStep += 1;
			makeSQLValue($id);
			$this->exec("UPDATE $table SET $sortField = $nStep WHERE $key = $id");
		}
	}
	function selectKeys($key, $sql = '')
	{
		$ids			= array();
		$key			= makeField($key);
		$this->fields	= "$key AS id";
		$sql[]			= $this->sql;
		$res			= $this->dbLink->dbExec($this->makeSQL($sql), 0, 0);
		while($data = $this->dbLink->dbResult($res)) $ids[] = $data['id'];
		return implode(',', $ids);
/*
		$key	=	makeField($key);
		$this->fields	= "GROUP_CONCAT(DISTINCT $key SEPARATOR ',') AS ids";
		$res	= dbExec($this->makeSQL($sql), 0, 0, $this->dbLink);
		$data	= dbResult($res);
		return $data['ids'];
*/
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function execSQL($sql)	{ return $this->dbLink->dbExec($sql, 0, 0); }
	function exec($sql, $max = 0, $from = 0){
		$this->maxCount = $this->ndx = 0;
		return $this->res = $this->dbLink->dbExec($sql, $max, $from);
	}
	function next(){ 
		if ($this->max && $this->maxCount >= $this->max) return false;
		$this->maxCount++;
		$this->ndx++;
		$this->data = $this->dbLink->dbResult($this->res);
		return $this->rowCompact();
	}
	function rows()			{ return $this->dbLink->dbRows($this->res); }
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
		$table		= makeField($this->table());
		$group		= $this->group;

		if ($this->fields) $fields = $this->fields;
		else $fields = '*';

		if ($val = $where[':from'])
		{
			unset($where[':from']);

			$t = array();
			foreach($val as $name => $alias){
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
			
		if ($date)
			$where[]	= 'lastUpdate > '.makeSQLDate($date);
		
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
		if ($this->data['fields'] && !is_array($this->data['fields'])){
			$a = unserialize($this->data['fields']);
			if (is_array($a)) $this->data['fields'] = $a;
		}
		if ($this->data['document'] && !is_array($this->data['document'])){
			$a = unserialize($this->data['document']);
			if (is_array($a)) $this->data['document'] = $a;
		}
		reset($this->data);

		if (isset($this->cache)){
			$id	= $this->data[$this->key];
			$this->cache[$id] = $this->data;
		}

		return $this->data;
	}
	function update($data, $doLastUpdate = true)
	{
		$table	= $this->table();
		$key	= $this->key();
		$id	= makeIDS($data['id']);
		unset($data['id']);

		reset($data);
		while(list($field, $value)=each($data))
		{
			if (is_string($value)){
				if (function_exists('makeSQLLongDate') && ($date = makeSQLLongDate($value)))
				{
					$data[$field] = $date;
					continue;
				}
				if ($date = makeDateStamp($value)){
					$data[$field]=makeSQLDate($date);
					continue;
				};
			}
			makeSQLValue($data[$field]);
		}
//		print_r($data); die;

		if ($doLastUpdate) $data['lastUpdate']=makeSQLDate(time());
		if ($id){
			$k = makeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else
			$id = $this->insertRow($table, $data);
//echo mysql_error();			
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
		$table = makeField($table);
		$fields=''; $comma=''; $values='';
		foreach($array as $field => $value)
		{
			$field	= makeField($field);
			$fields	.= "$comma$field";
			$values	.= "$comma$value";
			$comma	= ',';
		}
		
		if ($bDelayed) $res = $this->dbLink->dbExec("INSERT DELAYED INTO $table ($fields) VALUES ($values)", 0, 0);
		$res =  $this->dbLink->dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0);

		unset($table);
		unset($fields);
		unset($values);

		return $res;
	}
	function updateRow($table, &$array, $sql)
	{
		reset($array);
		$table = makeField($table);
		$command=''; $comma='SET ';
		while(list($field, $value)=each($array)){
			$field	=makeField($field);
			$command.="$comma$field=$value";
			$comma	= ',';
		}
		return $this->execSQL("UPDATE $table $command $sql");
	}
	function folder($id = 0){
		if (!$id) $id = $this->id();
		if ($id){
			$fields= $this->data['fields'];
			if (!is_array($fields)) $fields = unserialize($fields);
			$path	= $fields['filepath'];
			if ($path) return $this->images.'/'.$path;
		}
		$userID = function_exists('userID')?userID():0;
		return $this->images.'/'.($id?$id:"new$userID");
	}
	function url($id=0)		{ return $this->url.($id?$id:$this->id()); }
};

function makeIDS($id, $separator = ',')
{
	if (!is_array($id)) $id = explode($separator, $id);
	foreach($id as $ndx => &$val)
	{
		$val = trim($val);
		if (preg_match('#^\d+$#', $val)){
			$val = (int)$val;
		}else{
			if ($val) makeSQLValue($val);
		}
		if (!$val) unset($id[$ndx]);
	}
	return implode($separator, $id);
}

function makeDateStamp($val){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $val, $v)){
		list(,$d,$m,$y) = $v;
		return time(0, 0, 0, $m, $d, $y);
	}else
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return time($h, $i, 0, $m, $d, $y);
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return time($h, $i, $s, $m, $d, $y);
	}
	return 0;
}
function dateStamp($val){
	if (!$val) return;
	return date('d.m.Y H:i', $val);
}

?>