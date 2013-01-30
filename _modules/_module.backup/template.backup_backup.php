<?
function backup_backup(&$db, $val, &$data)
{
	$backupName		= date('Y-m-d-H-i');
	$backupFolder	= localHostPath.'/_backup/'.$backupName;
	$note			= getValue('backupNote');
	
	if (testValue('backupNote')){
		delTree($backupFolder);
		makeDir($backupFolder);
		
		$options				= array();
		$options['backupImages']= getValue('backupImages');
		makeBackup($backupFolder, $options);
		
		$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
		$freeSpace		= "свободно места: <b>$freeSpace Мб.</b>";
		module('message', "Архивация завершена \"<b>$backupName</b>\", $freeSpace");
		$freeSpace		= '';
	}else{
		$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
		$freeSpace		= "<p>Свободно места: <b>$freeSpace Мб.</b></p>";
	}
	
	file_put_contents_safe("$backupFolder/note.txt", $note);
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
	$prefix = @$ini[':db']['prefix'];
	if (!$prefix)	$prefix = getSiteURL();
	if ($prefix)	$prefix .= '_';
	
	$bOK	= true;
	$fTable = fopen($ft = "$backupFolder/dbTable.sql",			"w");
	$fData 	= fopen($fd = "$backupFolder/dbTableData.txt.bin",	"w");
	
	$db = new dbRow();
	$db->exec("SHOW TABLE STATUS FROM `$dbName`");
	while($data = $db->next()){
		$name = $data['Name'];
		if (strncmp($name, strtolower($prefix), strlen($prefix))!=0) continue;
		$tableName	= str_replace($prefix, '', $name);
		$bOK &= makeInstallSQL($backupFolder, $prefix, $name, $fTable, $fData);
	}
	
	fclose($fTable);
	fclose($fData);
	
	@$bBackupImages = $options['backupImages'];
	if ($bBackupImages){
		copyFolder(images, "$backupFolder/images");
	}
}
function makeInstallSQL($backupFolder, $prefix, $name, &$fTable, &$fData)
{
	$db			= new dbRow();
	$tableName	= str_replace($prefix, '', $name);
	
	makeDir("$backupFolder/code");
	$fStruct = fopen("$backupFolder/code/table_$tableName.txt", 'w');
	$db->exec("DESCRIBE `$name`");
	fwrite($fStruct, '$'.$tableName." = array();\r\n");
	while($data = $db->next()){
		$split = '';
		fwrite($fStruct, '$'.$tableName."['$data[Field]']= array(");
		foreach($data as $n => $v){
			if ($n == 'Field') continue;
			$v = str_replace('\'', '\\\'', $v);
			fwrite($fStruct, "$split'$n'=>'$v'");
			$split = ', ';
		}
		fwrite($fStruct, ");\r\n");
	}
	fwrite($fStruct, 'dbAlterTable(\''.$tableName.'\', $'.$tableName.');'."\r\n");
	fclose($fStruct);

	$db->exec("SHOW CREATE TABLE `$name`");
	$data = $db->next();
	if (!$data) return;

	$sql = @$data['Create Table'];
	$sql = str_replace($name, "%dbPrefix%$tableName", $sql);
	if (!$sql) return;
	
	fwrite($fTable, "#\n#	table $tableName\n#\n$sql;\r\n");
	
	$ndx = 0;
	$db->table = $name;
	$db->open();
	while($data = $db->next()){
		if (!$ndx++){
			fwrite($fData, "#$tableName#\n");
			$split = '';
			while(list($field, $val)=each($data)){
				if (is_int($field)) continue;
				fwrite($fData, $split);
				fwrite($fData, $field);
				$split = "\t";
			}
			fwrite($fData, "\n");
			reset($data);
		}
		$split = '';
		while(list($field, $val)=each($data)){
			if (is_int($field)) continue;
			fwrite($fData, $split);
			$val = str_replace("\\", '\\\\', $val);
			$val = str_replace("\n", '\\n', $val);
			$val = str_replace("\t", '\\t', $val);
			fwrite($fData, $val);
			$split = "\t";
		}
		fwrite($fData, "\n");
	}
	return true;
}

?>