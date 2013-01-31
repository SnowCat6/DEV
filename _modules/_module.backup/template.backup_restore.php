<?
function backup_restore(&$db, $val, &$data)
{
	$backupName		= $data[1];
	$backupFolder	= localHostPath.'/_backup/'.$backupName;
	$bHasBackup		= is_dir($backupFolder);
	@$note	= file_get_contents("$backupFolder/note.txt");
	
	if ($bHasBackup && testValue('doBackupRestore')){
		if (testValue("backupRestoreYes")){
			if (backupRestore($backupFolder)){
				module('message', 'Восстановление завершено');
			}else{
				module('message:error', 'Ошибка восстановления');
			}
			clearCache();
		}else{
			module('message:error', 'Нажмите галочку для начала восстановления');
		}
		if (testValue('ajax')) return;
	}

	module('script:ajaxForm');
	$class = testValue("backupRestoreYes")?' checked="checked"':'';
?>
<h1>Восстановление резервной копии</h1>
<? if (!$bHasBackup){
	module('message:error', "Нет резервной копии в папке \"<b>$backupFolder</b>\"");
	return module('display:message');
}?>
<h2>{$backupName}</h2>
<blockquote><pre>{$note}</pre></blockquote>
{{display:message}}
<form action="<?= getURL("backup_$backupName")?>" method="post" class="admin ajaxForm">
<input type="hidden" name="doBackupRestore" />
<p><input name="backupRestoreYes" id="backupRestoreYes" type="checkbox" value="1"{!$class} /> <label for="backupRestoreYes">Восстановить сайт, все текущие данные будут уничтожены</label></p>
<div><input type="submit" value="Восстановить" class="button" /></div>
</form>
<? } ?>
<?
function backupRestore($backupFolder)
{
	define('restoreProcess', true);
	//	Удалим все таблицы базы данных
	restoreDeleteTables();
	//	Перреинициализируем базу данных
	modulesConfigure();
	$ini = getCacheValue($ini);
	event('config.end', $ini);
	restoreDbData("$backupFolder/dbTableData.txt.bin");

	$images	= is_dir("$path/images");
	if ($images){
		delTree(images);
		copyFolder("$path/images", images);
	}
	return true;
}
function restoreDeleteTables()
{
	$ini	= getCacheValue('ini');
	$dbName = @$ini[':db']['db'];
	$prefix = dbTablePrefix();

	$db = new dbRow();
	$ddb= new dbRow();
	$db->exec("SHOW TABLE STATUS FROM `$dbName`");
	while($data = $db->next())
	{
		$name = $data['Name'];
		if (strncmp(strtolower($name), strtolower($prefix), strlen($prefix)) != 0) continue;
		$ddb->exec("DROP TABLE `$name`");
	}
}
function restoreDbData($fileName)
{
	@$f = fopen($fileName, "r");
	if (!$f) return false;
	
	set_time_limit(0);
	$db			= new dbRow();
	$tableName 	= '';
	$colsName	= array();
	while($row = fgets($f, 1024*1024)){
		$row = explode("\t", rtrim($row));
		//	Skip empty rows
		if (!$row) continue;
		//	Table name
		if (count($row)==1 && $row[0][0]=='#'){
			$tableName = trim($row[0], '#');
			$colsName	= array();
			continue;
		}
		if (!$tableName) continue;
		//	Col names
		if (!$colsName){
			$colsName = $row;
			continue;
		}
		$data = array();
		while(list($ndx, $val)=each($row)){
			$colName= $colsName[$ndx];
			if ($val != 'NULL'){
				$val	= base64_decode($val);
				makeSQLValue($val);
			}
			$data[$colName] = $val;
		}
//		print_r($data);
		$db->insertRow(dbTableName($tableName), $data);
//		echo$tableName, ' ', mysql_error();
	}
}

?>