<?
$gIni	= getGlobalCacheValue('ini');
$gIni	= $gIni[':db'];
if (!is_array($gIni)) $gIni = array();

$ini	= getCacheValue('ini');
$dbIni	= $ini[':db'];

if ($dbIni['login'] || $dbIni['passw']){
	$dbIni['login']	= $dbIni['login'];
	$dbIni['passw']	= $dbIni['passw'];
}

foreach($gIni as $name => $val)
{
	if (array_key_exists($name, $dbIni)) continue;
	$dbIni[$name] = $val;
}

//
$dbName	= $dbIni['db'];
if (!$dbName)	$dbName	= siteFolder();
$dbName	= preg_replace('#[^a-zA-Z0-9_]#', '_', $dbName);
$dbName	= preg_replace('#_+#', '_', $dbName);
$dbIni['db']	= $dbName;

//
$dbPrefix	= $dbIni['prefix'];
if (!$dbPrefix)	$dbPrefix	= siteFolder();
$dbPrefix	= preg_replace('#[^a-zA-Z0-9_]#', '_', $dbPrefix);
$dbPrefix	= preg_replace('#_+#', '_', $dbPrefix);
$dbPrefix	= rtrim($dbPrefix, '_');
$dbPrefix	.= '_';
$dbIni['prefix']	= $dbPrefix;

setCacheValue('dbIni', $dbIni);

function dbDeleteField($table, $field)
{
	$dbLink	= new dbRow();
	$dbLink	= $dbLink->dbLink;
	$table	= $dbLink->dbTableName($table);
	$dbLink->dbExec("ALTER TABLE `$table` DROP COLUMN `$field`");
}
//	fields $fields[name]=array{'type'=>'int', 'length'=>'11'};.....
function dbAlterTable($table, $fields, $dbEngine = '', $rowFormat = '')
{
	$dbLink	= new dbRow();
	$dbLink	= $dbLink->dbLink;
	$dbLink->connect(true);
	$table = $dbLink->dbTableName($table);

	if (!$dbEngine)	$dbEngine	= 'MyISAM';
	if (!$rowFormat)$rowFormat	= 'DYNAMIC';

	$dbFields	= getCacheValue('dbFields');
	if (!is_array($dbFields)) $dbFields = array();
	
	foreach($fields as $name => $f)
	{
		$m		= array();
		preg_match('#([\w\d_]+)(.*)#', $f['Type'], $m);
		$type	= $m[1];
		
		if ($type == 'array'){
			$fields[$name]['Type']	= 'mediumtext';
		}
		$dbFields[$table][$name] = $type;
	}
	setCacheValue('dbFields', $dbFields);
	
//define('_debug_', true);
	$tableFields= array();
	$alter		= array();
	$rs			= $dbLink->dbExec("DESCRIBE `$table`");
	//	Таблица существует, обновить структуру
	if ($rs)
	{
		$rs2	= $dbLink->dbExec("SHOW CREATE TABLE `$table`");
		$data	= $dbLink->dbResult($rs2);
		$create	= $data['Create Table'];
		$keys	= dbParseKeys($create);
		//	Database engine
		$thisEngine		= dbParseValue('ENGINE',	$create);
		if ($thisEngine != $dbEngine){
			$dbLink->dbExec("ALTER TABLE `$table` ENGINE=$dbEngine");;
		}
		//	Database row format
		$thisRowFormat	= dbParseValue('ROW_FORMAT', $create);
		if ($thisRowFormat != $rowFormat){
			$dbLink->dbExec("ALTER TABLE `$table` ROW_FORMAT=$rowFormat");;
		}
		//	Database keys and fields
		while($data = $dbLink->dbResult($rs))
		{
			$name	= $data['Field'];
			$tableFields[$name]	= $name;
			$f		= $fields[$name];
			if (!$f) continue;
			unset($fields[$name]);
			
			$f['Field']	= $name;
			dbAlterCheckField($alter["CHANGE COLUMN `$name` `$name`"], $f, $data, false, $keys);
		}
		//	Добавить несуществующие колонки
		foreach($fields as $name => $f){
			$data 		= array();
			$f['Field'] = $name;
			dbAlterCheckField($alter["ADD COLUMN `$name`"], $f, $data);
		}
		//	Создать SQL запрос
		$sql = array();
		foreach($alter as $name=>$value){
			if (!$value) continue;
			$value = implode(' ', $value);
			$sql[] = "$name $value";
		}
		//	Выполнить обновления БД
		if ($sql){
			$sql = implode(', ', $sql);
//			echo("ALTER TABLE $table $sql");
			$dbLink->dbExec("ALTER TABLE $table $sql");
			module('message:sql', "Updated table `$table`");
//			echo mysql_error();
		}
		$dbLink->dbExec("OPTIMIZE TABLE $table");
		return $tableFields;
	}
	//	Create Table
	foreach($fields as $name => $f)
	{
		$tableFields[$name]	= $name;
		$data 		= array();
		$f['Field'] = $name;
		dbAlterCheckField($alter["`$name`"], $f, $data, true);
	}
	$sql = array();
	//	Создать SQL
	foreach($alter as $name=>$value){
		if (!$value) continue;
		$value = implode(' ', $value);
		$sql[] = "$name $value";
	}
	if (!$sql) return;
	$sql = implode(', ', $sql);
	//	Выполнить создание таблицы
	$dbLink->dbExec("CREATE TABLE $table ($sql) COLLATE='utf8_general_ci' ENGINE=$dbEngine ROW_FORMAT=$rowFormat;");
	module('message:sql', "Created table `$table`");
	return $tableFields;
}
function dbAlterCheckField(&$alter, &$need, &$now, $bCreate = false, $keys = NULL)
{	
	//	Задать стандартные значени
	if (!isset($need['Type']))	$need['Type']	= $now['Type'];
	if (!isset($need['Null']))	$need['Null']	= 'YES';
	if (!isset($need['Key']))	$need['Key']	= '';
	if (!isset($need['Extra']))	$need['Extra']	= '';
	if (!isset($need['Default']))$need['Default']=NULL;

	//	Проверить еслть ли ищменения
	$bChanged = false;
	$bChanged |= $need['Type'] != $now['Type'];
	$bChanged |= isset($need['Null']) 	&& $need['Null'] 		!= $now['Null'];
	$bChanged |= isset($need['Default'])&& $need['Default'] 	!= $now['Default'];
	$bChanged |= isset($need['Key'])	&& $need['Key'] 		!= $now['Key'];
	//	Если нет изменений, далее не проверять
	if (!$bChanged) return;

	//	Добавить тип данных
	$alter[]= $need['Type'];

	//	Добавить возможность NULL щначений
	$n		= $need['Null'];
	$alter[]= $n=='NO'?'NOT NULL':'NULL';

	//	Значение по умолчанию
	$n = $need['Default'];
	if ($n != NULL)
	{
		if ($n == '(NULL)')	$n = 'NULL';
		else if ($n == '') 	$n = "''";
		else $n = "'$n'";
		
		$alter[] = "DEFAULT $n";
	}

	$n = $need['Extra'];
	if ($n != NULL){
		$alter[] = "$n";
	}
	
	//	Проверить соттветствия ключей
	$n = $need['Key'];
	if ($n && $n != $now['Key'])
	{
		$ndxName = $need['Field'];

		if ($n == 'PRI')		$n = 'PRIMARY KEY';
		else if ($n == 'UNI')	$n = 'UNIQUE INDEX';
		else{
			//	Если индексируется текстовое поле - FULLTEXT инекс
			if ($need['Type'] == 'text') $n = 'FULLTEXT INDEX';
			else $n = 'INDEX';
		}

		//	Если синаксис создания таблицы
		if ($bCreate){
			$alter[] = ", $n `$ndxName` (`$ndxName`)";
		}else{
			//	Если удалить ключ
			if ($keys && $keys[$ndxName] && $now['Key'])	$alter[] = ", DROP INDEX `$ndxName`";
			//	Добавить ключ
			$alter[] = ", ADD $n `$ndxName` (`$ndxName`)";
		}
	}
}

function dbParseValue($name, $code)
{
	if (!preg_match("#$name\s*=\s*([^\s]+)#", $code, $var)) return NULL;
	return $var[1];
}
function dbParseKeys($code)
{
	if (!preg_match_all('#KEY\s+`([^`]+)`\s*\(\s*`([^`]+)`\s*\)#', $code, $val)) return;
	$res	= array();
	foreach($val[1] as $ix => $keyName){
		$res[$keyName]	= $val[2][$ix];
	};
	return $res;
}
?>
