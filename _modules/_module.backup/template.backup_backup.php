<?
function backup_backup(&$db, $val, &$data)
{
	$backupName		= date('Y-m-d-H-i', mktime());
	$backupFolder	= localHostPath.'/_backup/'.$backupName;
	$note			= getValue('backupNote');
	$passw			= getValue('backupPassword');
	
	$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
	$freeSpace		= "<p>Свободно: <b>$freeSpace Мб.</b></p>";
	
	if (!access('write', 'backup')){
			module('message:error', 'Недостаточно прав доступа');
	}else
	if (testValue('backupNote'))
	{
		delTree($backupFolder);
		makeDir($backupFolder);
		
		$options				= array();
		$options['backupImages']= getValue('backupImages');
		
		if ($passw) file_put_contents_safe("$backupFolder/password.bin", md5($passw));
		file_put_contents_safe("$backupFolder/note.txt", $note);
		@$bOK = makeBackup($backupFolder, $options);
		if (!$bOK) delTree($backupFolder);
		
		$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
		$freeSpace		= "свободно: <b>$freeSpace Мб.</b>";
		if ($bOK){
			module('message', "Архивация завершена \"<b>$backupName</b>\", $freeSpace");
		}else{
			module('message:error', "Ошибка архивации \"<b>$backupName</b>\", $freeSpace");
		}
		$freeSpace		= '';
	}
?>
<h1>Архивация сайта</h1>
{!$freeSpace}
{{display:message}}
<?  } ?>
<?
//	make site backup
function makeBackup($backupFolder, $options)
{
//	[table name][col]=>columns // `name` type, SQL commands
//	[table name][db]=>dbRow object
	$ini	= getCacheValue('ini');
	$dbName = @$ini[':db']['db'];
	$prefix	= dbTablePrefix();
	
	$bOK	= true;
	makeDir("$backupFolder/code");
	$fTable= fopen("$backupFolder/dbTable.sql",			"w");
	$fData = fopen("$backupFolder/dbTableData.txt.bin",	"w");
	
	if ($fTable && $fData){
		$db = new dbRow();
		$db->exec("SHOW TABLE STATUS FROM `$dbName`");
		while($data = $db->next())
		{
			$name = $data['Name'];
			if (strncmp(strtolower($name), strtolower($prefix), strlen($prefix)) != 0) continue;
			
			$bOK &= makeInstallSQL($prefix, $name, $fTable, $fData, &$fStruct);

			$tableName	= str_replace($prefix, '', $name);
			$fStruct	= fopen("$backupFolder/code/table_$tableName.txt", 'w');
			$bOK &= makeInstallStruct($prefix, $name, $fStruct);
			$bOK &= fclose($fStruct);
		}
	}
	
	$bOK &= fclose($fTable);
	$bOK &= fclose($fData);
	if (!$bOK) return false;
	
	@$bBackupImages = $options['backupImages'];
	if ($bBackupImages){
		copyFolder(images, "$backupFolder/images", '^thumb');
	}
	return $bOK;
}
function makeInstallStruct($prefix, $name, &$fStruct)
{
	$tableName	= str_replace($prefix, '', $name);
	
	$db			= new dbRow();
	$db->exec("DESCRIBE `$name`");
	if (!fwrite($fStruct, '$'."$tableName = array();\r\n")) return false;

	while($data = $db->next())
	{
		if (!fwrite($fStruct, '$'.$tableName."['$data[Field]']= array(")) return false;
		
		$split = '';
		foreach($data as $n => $v){
			if ($n == 'Field') continue;
			$v = str_replace('\'', '\\\'', $v);
			if (!fwrite($fStruct, "$split'$n'=>'$v'")) return false;
			$split = ', ';
		}
		if (!fwrite($fStruct, ");\r\n")) return false;
	}
	return fwrite($fStruct, "dbAlterTable('$tableName', ".'$'."$tableName);\r\n") != false;
}

function makeInstallSQL($prefix, $name, &$fTable, &$fData, &$fStruct)
{
	$db			= new dbRow();
	$tableName	= str_replace($prefix, '', $name);

	$db->exec("SHOW CREATE TABLE `$name`");
	$data = $db->next();
	if (!$data) return true;

	$sql = @$data['Create Table'];
	$sql = str_replace($name, "%dbPrefix%$tableName", $sql);
	if (!$sql) return true;
	
	if (!fwrite($fTable, "#\n#	table $tableName\n#\n$sql;\r\n")) return false;

	if (!fwrite($fData, "#$tableName#\n")) return false;
	
	$split = '';
	$db->exec("DESCRIBE `$name`");
	while($data = $db->next()){
		if (!fwrite($fData, "$split$data[Field]")) return false;
		$split = "\t";
	}
	if (!fwrite($fData, "\n")) return false;

	$db->table = $name;
	$db->open();
	while($data = $db->next())
	{
		$split = '';
		while(list($field, $val) = each($data))
		{
			if (is_int($field)) continue;
			if (is_null($val))
				$val = 'NULL';
			else 
			if (preg_match('#^[\d+]$#', $val)){
				if (!$val) $val = "zero";
			}else
				$val = base64_encode($val);
			
			if ($split && !fwrite($fData, $split))	return false;
			if ($val && !fwrite($fData, $val))		return false;
			
			$split = "\t";
		}
		if (!fwrite($fData, "\n")) return false;
	}
	return true;
}

?>