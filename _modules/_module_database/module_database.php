<?
//	Основной класс базовых функций работы с БД
class dbConfig
{
	var		$dbLink;	//	Объект MySQLi
	var		$ini;		//	Конфигурация базы данных, логин, пароль
	var		$connected;	//	База данных подключена
	var		$dbCreated;	//	Создание базы данных было (выполнение комманды создания БД)
	//	Созать объект MySQLi или использовать внешний
	function create($dbLink = NULL){
		return $this->dbLink 	= $dbLink?$dbLink:new MySQLi();
	}
	//	Получить конфигурацию базы данных, логин пароль и прочее.
	function getConfig()
	{
		if (isset($this->ini)) return $this->ini;
		//	Смотрим локальные настройки базы данных
		$ini		= getCacheValue('ini');
		$dbIni		= $ini[':db'];
		if (is_array($dbIni)){
			return $this->ini = $dbIni;
		}
		//	Если их нет, пробуем глобальные
		$ini	= getGlobalCacheValue('ini');
		$dbIni	= $ini[':db'];
		return $this->ini = $dbIni;
	}
	//	Соеденить с базой данных, если надо - то создать базу данных
	function dbConnect($bCreateDatabase = false)
	{
		return $this->dbConnectEx($this->getConfig(), $bCreateDatabase);
	}
	//	Соеденить с базой данных по передонному конфигурационному фвйлу
	function dbConnectEx($dbIni, $bCreateDatabase = false)
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
			$this->connected= true;
		}
		
		$db		= $dbIni['db'];
		//	Создать базы данных
		if ($bCreateDatabase && !$this->dbCreated){
			$this->dbCreated = true;
			$this->dbExec("CREATE DATABASE `$db`");
		}
		if ($bConnected) return true;
		//	Сконфигурировать базу
		$this->dbExec("SET NAMES UTF8");
		$this->dbSelect($db);
		
		return true;
	}
	//	Получить префикс таблиц для уникального наименования сайта в базе данных
	function dbTablePrefix()
	{
		$dbConfig	= $this->getConfig();
		$prefix		= $dbConfig['prefix'];
		
		$constName	= "tablePrefix_$prefix";
		if (defined($constName)) return constant($constName);

		if (!$prefix) $prefix = preg_replace('#[^\d\w]+#', '_', getSiteURL());
		
		$prefix	= $prefix.'_';
		define($constName, $prefix);
		return $prefix;
	}
	//	Получить полное название таблицы прибавив префикс
	function dbTableName($name){
		$prefix = $this->dbTablePrefix();
		return "$prefix$name";
	}
	//	Выполнить SQL запрос
	function dbExec($sql, $rows = 0, $from = 0, &$dbLink = NULL)
	{
		//	Соеденить
		$this->dbConnect();
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
	function dbSelect($db)			{ return $this->dbLink->select_db($db); }
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
		$this->dbConnect();
		$val = $this->dbLink->escape_string($val);
		return $val;
	}
};
//	Соновной класс для работы с БД
class dbRow
{
	var $dbLink;
	var $rows;
//	main functions
	function dbRow($table = '', $key = '', $dbLink = NULL)
	{
		$lnk	= &$GLOBALS['_CONFIG']['dbLink'];
		if (!$dbLink) $dbLink = $lnk;
		if (!$dbLink){
			$dbLink	= new dbConfig();
			$dbLink->create();
			$lnk	= $dbLink;
		}
		$this->dbLink	= $dbLink;
		$this->table	= $this->dbLink->dbTableName($table);;
		$this->key 		= $key;
		$this->max		= 0;
		$this->rows		= 0;
	}
	function getConfig(){
		return $this->dbLink->getConfig($val);
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
		$this->fields	= '';
		$this->data		= $data;
		$this->setCacheValue();
	}
	function setCacheData($id, &$data){
		$this->setData($data);
	}
	function resetCache($id){
		if (isset($this->cache)) $this->cache[$id] = NULL;
	}
	function clearCache($id = NULL){
		if (isset($this->cache)){
			if ($id) $this->cache[$id] = NULL;
			else $this->cache = array();
			memClear($this->table());
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
			$k		= $this->table().":$id";
			$this->data	= memGet($k);
			if ($this->data) return $this->data;
			$this->data	= $this->cache[$id];
			if ($this->data) return $this->data;
		}
		
		$key	= makeField($this->key());
		$this->open("$key = $id");
		$data	= $this->next();
		
		if (isset($this->cache)){
			if (memSet($k, $data)) return $data;
		}
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
	function sortByKey($sortField, $orderTable, $startIndex = 0)
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
	function selectKeys($key, $sql = '', $bStringResult = true)
	{
		$ids			= array();
		$key			= makeField($key);
		$this->fields	= "$key AS id";
		$sql[]			= $this->sql;
		$res			= $this->dbLink->dbExec($this->makeSQL($sql), 0, 0);
		while($data = $this->dbLink->dbResult($res)) $ids[] = $data['id'];
		return $bStringResult?implode(',', $ids):$ids;
	}
	function selectKeys2table($key, $sql)
	{
		$key		= makeField($key);
		$tmpName	= 'tmp_'.md5(rand()+time());
		$q	= "CREATE TABLE `$tmpName` ($key INT UNSIGNED NOT NULL) ENGINE=MEMORY";
		
		$this->fields	= "$key AS id";
		$sql[]			= $this->sql;
		$sql			= $this->makeRawSQL($sql);
/*
		$sql['action']	= 'SELECT';
		$sql['fields']	= $fields;
		$sql['from']	= $table;
		$sql['join']	= $join;
		$sql['where']	= $where;
		$sql['group']	= $group;
		$sql['order']	= $order;
*/
		$sql			= "INSERT INTO `$tmpName` $sql[action] $sql[fields] FROM $sql[from] $sql[join] $sql[where] $sql[group] $sql[order]";
		echo $q;
		echo $sql;
//		$res			= $this->dbLink->dbExec($sql, 0, 0);

		return $tmpName;
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function execSQL($sql)	{
		return $this->dbLink->dbExec($sql, 0, 0);
	}
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
		$table		= makeField($this->table());
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
		$this->setCacheValue(true);

		return $this->data;
	}
	function setCacheValue($bRemoveTop = false)
	{
		if (!isset($this->cache) ||
		 ($this->fields != '' && !is_int(strpos($this->fields, '*')))) return;
		 
		$key	= $this->key;
		if (count($this->cache) > 10){
			if ($bRemoveTop){
				$k	= array_pop($this->cache);
			}else{
				$k	= array_shift($this->cache);
			}
			$table	= $this->table();
			$count	= count($this->cache);
			m('message:trace', "db cache $table clear, total $count:$k[$key]");
		}
		$id	= $this->data[$key];
		$this->cache[$id] = $this->data;
	}
	function update($data, $doLastUpdate = true)
	{
		$table	= $this->table();
		$key	= $this->key();
		$id		= makeIDS($data['id']);
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
		}else{
			$id = $this->insertRow($table, $data);
		}
		$this->resetCache($id);
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
		else $res =  $this->dbLink->dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0);

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
	function url($id = 0)		{ return $this->url.($id?$id:$this->id()); }
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
function makeSQLValue(&$val)
{
	switch(gettype($val)){
	case 'int': 	break;
	case 'float':
	case 'double':
		$val = str_replace(',', '.', $val);
	 	break;
	case 'NULL':
		$val = 'NULL';
		break;
	case 'array':
		$val = serialize($val);
	default:
		if (strncmp($val, 'FROM_UNIXTIME(', 14)==0) break;
		if (strncmp($val, 'DATE_ADD(', 9)==0) break;
		$db	= new dbRow();
		$val= $db->dbLink->escape_string($val);
		$val= "'$val'";
		break;
	}
}
//	Подготавливаются данные в соотвествии с правилами SQL
function sqlDate($val)		{ return date('Y-m-d H:i:s', (int)$val); }
function makeSQLDate($val)	{ $val = sqlDate($val); return "DATE_ADD('$val', INTERVAL 0 DAY)"; }
function makeField($val)	{ return "`$val`"; }
function makeDate($val)
{
	// mysql date looks like "yyyy-mm-dd hh:mm:ss"
	$year	= (int)substr($val, 0, 4);
	$month	= (int)substr($val, 5, 2);
	$day	= (int)substr($val, 8, 2);
	$hour	= (int)substr($val, 11, 2);
	$min	= (int)substr($val, 14, 2);
	$sec	= (int)substr($val, 17, 2);
	if (!$year) return NULL;
	
	// Warning: mktime uses a strange order of arguments
	$d = mktime($hour, $min, $sec, $month, $day, $year);
	if ($d < 0) $d = NULL;
	return $d;
}
//	dd-mm-yy h:i:s
function makeSQLLongDate($dateStamp){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $dateStamp, $v)){
		list(,$d,$m,$y) = $v;
		return "DATE_ADD('$y-$m-$d', INTERVAL 0 SECOND)";
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $dateStamp, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return "DATE_ADD('$y-$m-$d $h:$i:0', INTERVAL 0 SECOND)";
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $dateStamp, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return "DATE_ADD('$y-$m-$d $h:$i:$s', INTERVAL 0 SECOND)";
	}
	return;
}
//	dd-mm-yy h:i:s
function makeLongDate($dateStamp, $bFullDate = false){
	// mysql date looks like "yyyy-mm-dd hh:mm:ss"
	$year	= (int)substr($dateStamp, 0, 4);
	$month	= (int)substr($dateStamp, 5, 2);
	$day	= (int)substr($dateStamp, 8, 2);
	$hour	= (int)substr($dateStamp, 11, 2);
	$min	= (int)substr($dateStamp, 14, 2);
	$sec	= (int)substr($dateStamp, 17, 2);
	if (!$year) return;
	return sprintf($bFullDate?"%02d.%02d.%04d %02d:%02d:%02d":"%02d.%02d.%04d", $day,$month,$year,$hour,$min,$sec);
}


?>