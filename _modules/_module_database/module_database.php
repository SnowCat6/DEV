<?
class dbRow
{
//	main functions
	function dbRow($table = '', $key = '', $dbLink = 0, $alter = NULL)
	{
		$this->max		= 0;
		$this->table	= dbTableName($table);;
		$this->key 		= $key;
		$this->dbLink 	= $dbLink?$dbLink:dbConnect();
		if ($alter) $this->alter($alter);
	}
	function __destruct()	{ @mysql_free_result ($this->res); }
	function reset()		{ $this->order = $this->group = $this->fields = ''; }
	function setCache(){
		if (!isset($this->cache)){
			@$cache	= &$GLOBALS['_CONFIG'];
			@$cache	= &$cache['dbCache'];
			@$cache	= &$cache[$this->table];
			if (!isset($cache)) $cache = array();
			$this->cache = &$cache;
		}
	}
	function resetCache($id){
		if (isset($this->cache)) $this->cache[$id] = NULL;
	}
	function clearCache(){
		if (isset($this->cache)) $this->cache = array();
	}
	function open($where='', $max=0, $from=0, $date=0)
	{
		return @$this->exec($this->makeSQL($where, $date), $max, $from);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		$key 	= makeField($this->key());
		return $this->open("$key IN ($ids)");
	}
	function openID($id)
	{
		$id		= (int)$id;
		if (isset($this->cache)){
			@$data = $this->cache[$id];
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
	function sortByKey($sortField, &$orderTable)
	{
		if (!is_array($orderTable)) return;
		
		$sortField	= makeField($sortField);
		$key		= $this->key();
		$table		= $this->table();
		
		$nStep	= 0;
		$sql	= '';
		foreach($orderTable as $id){
			$nStep += 1;
			makeSQLValue($id);
			$this->exec("UPDATE $table SET $sortField = $nStep WHERE $key = $id");
		}
	}
	function selectKeys($key, $sql = '')
	{
		$key	= makeField($key);
		$table	= $this->table();
		if (is_array($sql)) $sql = implode(' AND ', $sql);
		if ($sql) $sql = " WHERE $sql";

		$res = dbExec("SELECT GROUP_CONCAT(DISTINCT $key SEPARATOR ', ') AS ids FROM $table $sql", 0, 0, $this->dbLink);
		$data= dbResult($res);
		return @$data['ids'];
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function execSQL($sql)	{ return dbExec($sql, 0, 0, $this->dbLink); }
	function exec($sql, $max=0, $from=0){
		$this->maxCount = $this->ndx = 0;
		return $this->res = dbExec($sql, $max, $from, $this->dbLink);
	}
	function next()			{ 
		if ($this->max && $this->maxCount >= $this->max) return false;
		$this->maxCount++;
		$this->ndx++;
		$this->data = dbResult($this->res);
		return $this->rowCompact();
	}
	function rows()			{ return @dbRows($this->res); }
	function seek($row)		{ @dbRowTo($this->res, $row); }
	function id()			{ return @$this->data[$this->key()]; }
	function makeSQL($where, $date = 0)
	{
		$table = makeField($this->table());
		
		if (@$this->fields) $fields = $this->fields;
		else $fields = '*';
		
		@$group = $this->group;
		
		if (is_array($where)){
			if (@$val = $where[':from'])
			{
				unset($where[':from']);
				$table = array();
				foreach($val as $tableName => $tableAlias){
					$table[] = dbTableName($tableName). " $tableAlias";
				}
				$table = implode(', ', $table);
			}
			if (@$val = $where[':fields']){
				unset($where[':fields']);
				$fields = $val;
			}
			if (@$val = $where[':group']){
				unset($where[':group']);
				$group = $val;
			}
			$where = implode(' AND ', $where);
		}
		
		if ($where) $where = "($where)";
		
		if ($date){
			if ($where) $where .= ' AND ';
			$where .= 'lastUpdate > '.makeSQLDate($date);
		}
		
		if (@$sql=$this->sql) $where.= $where?" AND $sql":$sql;
		if ($where) $where = "WHERE $where";
		if (@$order = $this->order) $order = "ORDER BY $order";
		if ($group)	$group = "GROUP BY $group";
		
		return "SELECT $fields FROM $table $where $group $order";
	}
	function rowCompact(){
		if (@$this->data['fields'] && !is_array($this->data['fields'])){
			$a = unserialize($this->data['fields']);
			if (is_array($a)) $this->data['fields'] = $a;
		}
		if (@$this->data['document'] && !is_array($this->data['document'])){
			$a = unserialize($this->data['document']);
			if (is_array($a)) $this->data['document'] = $a;
		}
		@reset($this->data);

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
		@$id	= makeIDS($data['id']);
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

		if ($doLastUpdate) $data['lastUpdate']=makeSQLDate(mktime());
		if ($id){
			$k = makeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else
			$id = $this->insertRow($table, $data);
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
		if (preg_match('#^\d+$#', $val)){
			$val = (int)$val;
		}else{
			if ($val) makeSQLValue($val);
		}
		if ($val) $result[$val] = $val;
	}
	if (count($result))	return implode(',',$result);
	return 0;
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