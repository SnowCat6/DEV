<?
//	Класс для манипуляции базой данных MySQL
//	Open database
function dbName(){
	$ini	= getCacheValue('ini');
	@$prefix= $ini[':db']['prefix'];
	if (!$prefix) $prefix = getSiteURL();
	return $prefix;
}
function dbTableName($name){
	$prefix = dbName();
	return $prefix?$prefix.'_'.$name:$name;
};
function dbConnect()
{
	if (defined('dbConnect')) return $GLOBALS['dbConnection'];
	define('dbConnect', true);

	$ini		= getCacheValue('ini');
	@$dbhost	= $ini[':db']['host'];
	@$dbuser	= $ini[':db']['login'];
	@$dbpass	= $ini[':db']['passw'];
	@$db		= $ini[':db']['db'];

	$GLOBALS['dbConnection'] = mysql_connect($dbhost, $dbuser, $dbpass);
	if (mysql_error()){
		module('message:sql:error', mysql_error());
		module('message:error', 'Ошибка открытия базы данных.');
		return;
	}
//	@dbExec("SET character_set_results = 'cp1251'");
//	@dbExec("SET character_set_client = 'cp1251'");
	@dbExec("CREATE DATABASE `$db`");
	@dbExec("SET NAMES UTF8");
	dbSelect($db, $GLOBALS['dbConnection']);
	return $GLOBALS['dbConnection'];
}

function dbExec($sql, $rows=0, $from=0, &$dbLink = NULL){// echo $sql;
	if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";
	module('message:sql:trace', $sql);
	$res = @mysql_query($rows?"$sql LIMIT $from, $rows":$sql);
	module('message:sql:error', mysql_error());
	return $res;
}
function dbSelect($db, &$dbLink)	{ return mysql_select_db($db); }
function dbRows($id)				{ return mysql_num_rows($id);}
function dbResult($id)				{ return @mysql_fetch_array($id, MYSQL_ASSOC);}
function dbRowTo($id, $row)			{ return @mysql_data_seek($id, $row);}
function dbExecIns($sql, $rows=0, &$dbLink)	{ dbExec($sql, $rows, 0, $dbLink); return mysql_insert_id(); }
function dbExecQuery($sql, &$dbLink){ 
	$err= array();
	$q	= explode(";\r\n", $sql);
	while(list(,$sql)=each($q)){
		if (!$sql) continue;
		if (dbExec($sql, 0, 0, $dbLink)) continue;
		$e 		= mysql_error($dbLink);
		$err[] 	= $e;
	}
	return $err;
}

//	Подготавливаются данные в соотвествии с правилами SQL
function makeSQLValue(&$val){
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
		$val=serialize($val);
	default:
		if (strncmp($val, 'FROM_UNIXTIME(', 14)==0) break;
		if (strncmp($val, 'DATE_ADD(', 9)==0) break;
		$val = @mysql_real_escape_string($val);
		$val = "'$val'";
		break;
	}
}
function sqlDate($val)		{ return date('Y-m-d H:i:s', (int)$val); }
function makeSQLDate($val)	{ return "FROM_UNIXTIME($val)"; }
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
	if (!$year) return 0;
	
	// Warning: mktime uses a strange order of arguments
	@$d = mktime($hour, $min, $sec, $month, $day, $year);
	if ($d < 0) $d = 0;
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

//	fields $fields[name]=array{'type'=>'int', 'length'=>'11'};.....
function dbAlterTable($table, $fields, $bUsePrefix = true)
{
	dbConnect();
//define('_debug_', true);
	if ($bUsePrefix) $table = dbTableName($table);

	$alter	= array();
	$rs		= dbExec("DESCRIBE $table");
	if ($rs){
		while($data = dbResult($rs))
		{
			$name	= $data['Field'];
			@$f 	= $fields[$name];
			if (!$f) continue;
			
			$f['Field'] = $name;
			dbAlterCheckField($alter["CHANGE COLUMN `$name` `$name`"], $f, $data);
			unset($fields[$data['Field']]);
//			print_r($f);
		}
		
		foreach($fields as $name => $f){
			$data 		= array();
			$f['Field'] = $name;
			dbAlterCheckField($alter["ADD COLUMN `$name`"], $f, $data);
		}
		
		$sql = array();
		foreach($alter as $name=>$value){
			if (!$value) continue;
			$value = implode(' ', $value);
			$sql[] = "$name $value";
		}
		if (!$sql) return;
		
		$sql = implode(', ', $sql);
//		echo("ALTER TABLE $table $sql");
		dbExec("ALTER TABLE $table $sql");
		module('message:sql', "Updated table `$table`");
//		echo mysql_error();
		return;
	}
	//	Create Table
	foreach($fields as $name => $f){
		$data 		= array();
		$f['Field'] = $name;
		dbAlterCheckField($alter["`$name`"], $f, $data, true);
	}
	$sql = array();
	foreach($alter as $name=>$value){
		if (!$value) continue;
		$value = implode(' ', $value);
		$sql[] = "$name $value";
	}
	if (!$sql) return;
	$sql = implode(', ', $sql);
	//	CREATE TABLE `1` (  `1` INT(10) NULL ) COLLATE='cp1251_general_ci' ENGINE=InnoDB ROW_FORMAT=DEFAULT;
	dbExec("CREATE TABLE $table ($sql) COLLATE='utf8_general_ci' ENGINE=MyISAM ROW_FORMAT=FIXED;");
	module('message:sql', "Created table `$table`");
}
function dbAlterCheckField(&$alter, &$need, &$now, $bCreate = false)
{	
	if (!isset($need['Type']))	@$need['Type']	= $now['Type'];
	if (!isset($need['Null']))	$need['Null']	= 'YES';
	if (!isset($need['Key']))	$need['Key']	= '';
	if (!isset($need['Extra']))	$need['Extra']	= '';
	if (!isset($need['Default']))$need['Default']=NULL;

	$bChanged = false;
	
//	print_r($now);
//	print_r($need);
	
	$bChanged |= @$need['Type'] != @$now['Type'];
	$bChanged |= isset($need['Null']) 	&& @$need['Null'] 		!= @$now['Null'];
	$bChanged |= isset($need['Default'])&& @$need['Default'] 	!= @$now['Default'];
	$bChanged |= isset($need['Key'])	&& @$need['Key'] 		!= @$now['Key'];
	
	if (!$bChanged) return;

	$alter[] = $need['Type'];

	$n = $need['Null'];
	$alter[] = $n=='NO'?'NOT NULL':'NULL';
	
	$n = $need['Default'];
	if ($n != NULL){
		if ($n == '(NULL)') $n = 'NULL';
		else
		if ($n == '') 		$n = "''";
		else
		$n = "'$n'";
		$alter[] = "DEFAULT $n";
	}
	
	$n = $need['Extra'];
	if ($n != NULL){
		$alter[] = "$n";
	}
	
	$n = $need['Key'];
	if ($n != $now['Key']){
		$ndxName = $need['Field'];

		if ($n){
			if ($n == 'PRI') $n = 'PRIMARY KEY';
			else
			if ($n == 'UNI') $n = 'UNIQUE INDEX';
			else{
				if ($need['Type'] == 'text') $n = 'FULLTEXT INDEX';
				else $n = 'INDEX';
			}
		}
		
		if ($bCreate){
			if ($n) $alter[] = ", $n `$ndxName` (`$ndxName`)";
		}else{
//			if ($now['Key'])$alter[] = ", DROP INDEX `$ndxName`";
			if ($n)		 	$alter[] = ", ADD $n `$ndxName` (`$ndxName`)";
		}
	}
}
?>