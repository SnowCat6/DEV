<?
//	fields $fields[name]=array{'type'=>'int', 'length'=>'11'};.....
function dbAlterTable($table, $fields, $bUsePrefix = true, $dbEngine = '', $rowFormat = '')
{
	$dbLink	= new dbRow();
	$dbLink	= $dbLink->dbLink;
	$dbLink->dbConnect(true);

	if ($bUsePrefix) $table = $dbLink->dbTableName($table);

	if (!$dbEngine)	$dbEngine	= 'MyISAM';
	if (!$rowFormat)$rowFormat	= 'DYNAMIC';
	
//define('_debug_', true);

	$alter	= array();
	$rs		= $dbLink->dbExec("DESCRIBE `$table`");
	if ($rs)
	{
		$rs2	= $dbLink->dbExec("SHOW CREATE TABLE `$table`");
		$data	= $dbLink->dbResult($rs2);
		//	Database engine
		$thisEngine		= dbParseValue('ENGINE',	$data['Create Table']);
		if ($thisEngine != $dbEngine){
			$dbLink->dbExec("ALTER TABLE `$table` ENGINE=$dbEngine");;
		}
		//	Database row format
		$thisRowFormat	= dbParseValue('ROW_FORMAT',$data['Create Table']);
		if ($thisRowFormat != $rowFormat){
			$dbLink->dbExec("ALTER TABLE `$table` ROW_FORMAT=$rowFormat");;
		}
		//	Database keys and fields
		while($data = $dbLink->dbResult($rs))
		{
			$name	= $data['Field'];
			$f 	= $fields[$name];
			if (!$f) continue;
			
			$f['Field'] = $name;
			dbAlterCheckField($alter["CHANGE COLUMN `$name` `$name`"], $f, $data);
			unset($fields[$data['Field']]);
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
		if ($sql){
			$sql = implode(', ', $sql);
//			echo("ALTER TABLE $table $sql");
			$dbLink->dbExec("ALTER TABLE $table $sql");
			module('message:sql', "Updated table `$table`");
//			echo mysql_error();
		}
		$dbLink->dbExec("OPTIMIZE TABLE $table");
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
	$dbLink->dbExec("CREATE TABLE $table ($sql) COLLATE='utf8_general_ci' ENGINE=$dbEngine ROW_FORMAT=$rowFormat;");
	module('message:sql', "Created table `$table`");
}
function dbAlterCheckField(&$alter, &$need, &$now, $bCreate = false)
{	
	if (!isset($need['Type']))	$need['Type']	= $now['Type'];
	if (!isset($need['Null']))	$need['Null']	= 'YES';
	if (!isset($need['Key']))	$need['Key']	= '';
	if (!isset($need['Extra']))	$need['Extra']	= '';
	if (!isset($need['Default']))$need['Default']=NULL;

	$bChanged = false;
	
//	print_r($now);
//	print_r($need);
	
	$bChanged |= $need['Type'] != $now['Type'];
	$bChanged |= isset($need['Null']) 	&& $need['Null'] 		!= $now['Null'];
	$bChanged |= isset($need['Default'])&& $need['Default'] 	!= $now['Default'];
	$bChanged |= isset($need['Key'])	&& $need['Key'] 		!= $now['Key'];
	
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

function dbParseValue($name, $code)
{
	if (!preg_match("#$name\s*=\s*([^\s]+)#", $code, $var)) return NULL;
	return $var[1];
}

?>
