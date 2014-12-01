<?
function backup_backup(&$db, $val, &$data)
{
	$backupName		= date('Y-m-d-H-i', time());
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
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
		
		$timeStart	= getmicrotime();
		$bOK		= makeBackup($backupFolder, $options);
		$time 		= round(getmicrotime() - $timeStart, 4);

		$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
		$freeSpace		= "свободно: <b>$freeSpace Мб.</b>";

		if ($bOK){
			$note		= "$note\r\nВремя архивирования $time";
			file_put_contents_safe("$backupFolder/note.txt", $note);
			if ($passw)
			{
				file_put_contents_safe("$backupFolder/password.bin", md5($passw));
				$url 	= getURLEx() . "index.php?URL=backup_$backupName.htm";
				
				module('message',
					"Архивация завершена \"<b>$backupName</b>\", $freeSpace<br />".
					"Ссылка для экстренного восстановления <div>".
					"<a href=\"$url\" target=\"new\"><b>$url</b></a></div>");
						
				module("backup:makeInstall", $backupName);
			}else{
				module('message', "Архивация завершена \"<b>$backupName</b>\", $freeSpace");
			}
		}else{
			delTree($backupFolder);
			module('message:error', "Ошибка архивации \"<b>$backupName</b>\", $freeSpace");
		}
		$freeSpace		= '';
	}
?>
{{page:title=Архивация сайта}}
{!$freeSpace}
{{display:message}}
<? } ?>
<?
//	make site backup
function makeBackup($backupFolder, $options)
{
	set_time_limit(0);

	$db			= new dbRow();
	$dbConfig	= $db->getConfig();
	$dbName		= $db->dbName();
	$prefix		= $db->dbTablePrefix();
	
	$exclude	= array();
	$ex			= getCacheValue(':backupExcludeTables');
	if (!is_array($ex)) $ex = array();
	foreach($ex as $tableName){
		$tableName				= "$prefix$tableName";
		$exclude[$tableName]	= dbEncString($db, $tableName);
	}
	$ex			= implode(',', $exclude);

	$bOK	= true;
	$fData	= fopen("$backupFolder/dbTableData.txt.bin",	"w");
	
	$sql	= array();
	$sql[]	= "`Name` LIKE '$prefix%'";
	if ($ex)	$sql[]	= "`Name` NOT IN ($ex)";
	$sql	= implode(' AND ', $sql);
	
	$db->exec("SHOW TABLE STATUS FROM `$dbName` WHERE $sql");
	while($data = $db->next())
	{
		$name = $data['Name'];
		$bOK &= makeInstallSQL($prefix, $name, $fData);
	}

	if (hasAccessRole('developer'))
	{
		makeDir("$backupFolder/code");
		$fTable= fopen("$backupFolder/code/dbTable.sql",				"w");

		$db->seek(0);
		while($data = $db->next())
		{
			$name = $data['Name'];
			$bOK &= makeInstallTable($prefix, $name, $fTable);
			
			$tableName	= str_replace($prefix, '', $name);
			$fStruct	= fopen("$backupFolder/code/table_$tableName.txt",	'w');
			$bOK &= makeInstallStruct($prefix, $name, $fStruct);
			$bOK &= fclose($fStruct);
		}
		
		$bOK &= fclose($fTable);
	}
	
	$bOK &= fclose($fData);
	if (!$bOK) return false;
	
	$bBackupImages = $options['backupImages'];
	if ($bBackupImages){
		copyFolder(images, "$backupFolder/images", '^thumb');
	}
	$configFile = localConfigName;
	if (is_file($configFile)) $bOK &= copy($configFile, "$backupFolder/config.ini") !== false;

	return $bOK;
}
//	Создает текстовой файл с инстукциями SQL для создания базы данных в ручном режиме, для разработчиков
function makeInstallTable($prefix, $name, &$fTable)
{
	$db			= new dbRow();
	$tableName	= str_replace($prefix, '', $name);
	
	$db->exec("SHOW CREATE TABLE `$name`");
	$data = $db->next();
	if (!$data) return true;

	$sql = $data['Create Table'];
	$sql = str_replace($name, "%dbPrefix%$tableName", $sql);
	if (!$sql) return true;

	if (!fwrite($fTable, "#\n#	table $tableName\n#\n$sql;\r\n")) return false;

	return true;
}
//	Создает PHP скрипт для создание страниц в автоматическом режиме, для разработчиков
function makeInstallStruct($prefix, $name, &$fStruct)
{
	$db			= new dbRow();
	$tableName	= str_replace($prefix, '', $name);
	
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
//	Создает дамп базы данных, каждая строка - строка таблицы, значения зашифрованы base64 и разделены знаком табуляции
function makeInstallSQL($prefix, $name, &$fData)
{
	$db			= new dbRow();
	$tableName	= str_replace($prefix, '', $name);

	if (!fwrite($fData, "#$tableName#\n")) return false;
	
	$split	= '';
	$fields	= array();
	$db->exec("DESCRIBE `$name`");
	while($data = $db->next()){
		$fields[]	= $data['Field'];
		if (!fwrite($fData, "$split$data[Field]")) return false;
		$split = "\t";
	}
	if (!fwrite($fData, "\n")) return false;

	$db->table = $name;
	$db->open();
	//	RAW table read
	while($data = $db->dbResult())
	{
		switch($tableName){
		case 'documents_tbl':
			$data['cache']		= '';	unset($data['cache']);
			$data['property']	= '';	unset($data['property']);
			break;
		}
		
		$split = '';
		$db->data = $data;
		foreach($fields as $field)
		{
			$val	= $data[$field];
			//	Если значение NULL, так и запишем
			if (is_null($val))
				$val = 'NULL';
			else	//	Если значение цифра, запомним как цифру, сделано для того, чтобы определить цифру ноль правильно.
			if (preg_match('#^[\d+]$#', $val)){
					//	Кодируем ноль словом, т.к. при восстановлении невозможно определить, цифра это или нет
				if (!$val) $val = "zero";
			}else{	//	Кодируем как base26 строку
				$val = base64_encode($val);
			}
			//	Записать разделитель колонок
			if ($split && !fwrite($fData, $split))	return false;
			//	Записать данные
			if ($val && !fwrite($fData, $val))		return false;
			
			$split = "\t";
		}
		if (!fwrite($fData, "\n")) return false;
	}
	return true;
}

?>