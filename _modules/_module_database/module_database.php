<?
class dbRow
{
//	main functions
	function dbRow($table = '', $key = '', $dbLink = 0, $alter = NULL){
		global $dbName;
		global $dbConnection;
		
		if (!defined('dbConnect')){
			define('dbConnect', true);
			$ini	= getCacheValue('ini');
			@$dbName= $ini[':db']['prefix'];
			if (!$dbName) $dbName = getSiteURL();
			@dbConnect(
				@$ini[':db']['host'],
				@$ini[':db']['login'],
				@$ini[':db']['passw'],
				@$ini[':db']['db']
				);
		}
		
		$this->max		= 0;
		$this->table	= $dbName?$dbName."_".$table:$table;
		$this->key 		= $key;
		$this->dbLink 	= $dbLink?$dbLink:$GLOBALS['dbConnection'];
		if ($alter) $this->alter($alter);
	}
	function __destruct()	{ @mysql_free_result ($this->res); }
	function reset()		{ $this->order = $this->group = $this->fields = ''; }
	function open($where='', $max=0, $from=0, $date=0)	{
		if (is_array($where)) $where = implode(' AND ', $where);
		return $this->doOpen($where, $max, $from, $date);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		$key 	= makeField($this->key());
		return $this->open("$key IN ($ids)");
	}
	function openID($id){
		$key= makeField($this->key());
		$id	= (int)$id;
		$this->open("$key=$id");
		return $this->next();
	}
	function delete($id)	{ $this->doDelete($id);	}
	function deleteByKey($key, $id){
		$key	= makeField($key);
		$table	= $this->table;
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $this->exec($sql);
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function exec($sql, $max=0, $from=0){
		$this->maxCount = $this->ndx = 0;
		return $this->res = dbExec($sql, $max, $from, $this->dbLink);
	}
	function execSQL($sql)	{ return dbExec($sql, 0, 0, $this->dbLink); }
	function next()			{ 
		if ($this->max && $this->maxCount >= $this->max) return false;
		$this->maxCount++;
		$this->ndx++;
		return $this->data = dbResult($this->res);
	}
	function rows()			{ return @dbRows($this->res); }
	function seek($row)		{ @dbRowTo($this->res, $row); }
//	base functions
	function doOpen($where='', $max=0, $from=0, $date=0){
		$table = makeField($this->table());
		if ($where) $where = "($where)";
		if ($date){
			if ($where) $where .= ' AND ';
			$where .= 'lastUpdate > '.makeSQLDate($date);
		}
		if (@$sql=$this->sql) $where.= $where?" AND $sql":$sql;
		if ($where) $where="WHERE $where";
		if (@$order=$this->order) $order="ORDER BY $order";
		if (@$group=$this->group) $group="GROUP BY $group";
		if (@$this->fields) $fields=$this->fields; else $fields='*';
		return @$this->exec("SELECT $fields FROM $table $where $group $order", $max, $from);
	}
	function rowCompact(){
		if (@$this->data['fields'] && !is_array($this->data['fields']))
			$this->data['fields'] = @unserialize($this->data['fields']);
		@reset($this->data);
		return $this->data;
	}
	function doDelete($id)
	{
		$table	=	$this->table();
		$key 	=	$this->key();
		$id		=	makeIDS($id);
		$key 	=	makeField($key);
		$table	=	makeField($table);
		$this->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	function id()		{ return @$this->data[$this->key()]; }
	function update($data, $doLastUpdate=true){
		$table=$this->table();
		$key = $this->key();
		@$id = makeIDS($data['id']);
		unset($data['id']);

		reset($data);
		while(list($field, $value)=each($data)){
			if (is_string($value)){
				if (function_exists('makeSQLLongDate') && ($date = makeSQLLongDate($value))){
					$data[$field]=$date;
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

		if ($doLastUpdate) $data['lastUpdate']=makeSQLDate(mktime());
		if ($id){
			$k=makeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else
			$id=$this->insertRow($table, $data);
//echo mysql_error();			
		return $id?$this->data[$key]=$id:0;
	}
	//	util functions
	function setValue($id, $field, $val, $doLastUpdate=true){
		$data=array('id'=>$id, $field=>$val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate=true){
		$data['id']=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, $array){
//	print_r($array); die;
		reset($array);
		$table = makeField($table);
		$fields=''; $comma=''; $values='';
		while(list($field, $value)=each($array)){
			$field=makeField($field);
			$fields.="$comma$field";
			$values.="$comma$value";
			$comma=',';
		}
		return dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0, $this->dbLink);
	}
	function updateRow($table, $array, $sql){
		reset($array);
		$table = makeField($table);
		$command=''; $comma='SET ';
		while(list($field, $value)=each($array)){
			$field=makeField($field);
			$command.="$comma$field=$value";
			$comma = ',';
		}
		return $this->execSQL("UPDATE $table $command $sql");
	}
	function folder($id=0){
		if (!$id) $id = $this->id();
		if ($id){
			@$fields= $this->data['fields'];
			if (!is_array($fields)) @$fields = unserialize($fields);
			@$path	= $fields['filepath'];
			if ($path) return $this->images.'/'.$path;
		}
		$userID = function_exists('userID')?userID():0;
		return $this->images.'/'.($id?$id:"new$userID");
	}
	function url($id=0)		{ return $this->url.($id?$id:$this->id()); }
	function alter($fields)	{ dbAlterTable($this->table, $fields, false); }
};

function makeIDS($id)
{
	if (!is_array($id)) $id=explode(',',$id);
	$result = array();
	reset($id);
	while(list($ndx, $val)=each($id))
	{
		if (strlen((int)$val) == strlen($val)){
			$val = (int)$val;
		}else{
			if ($val) makeSQLValue($val);
		}
		if ($val) $result[$val] = $val;
	}
	if (count($result))	return implode(',',$result);
	return 0;
}

////////////////////////////////////
//	создать папку по данному пути
function createDir($path){
	$dir	= '';
	$path	= explode('/',str_replace('\\', '/', $path));
	while(list(,$name)=each($path)) @mkdir($dir.="$name/");
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

?>