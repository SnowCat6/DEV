<?
class dbRestore
{
	//	Восстановить базу из архива
	static function makeRestore($backupFolder, $dbIni)
	{
		//	Определим константу, сообщающую, что идет восстановление
		define('restoreProcess', true);
		
		//	Получить конфигурационные данные БД или введенные пользователем, или из существующей конфигурации
		$db		= new dbRow();
		if (!$db->dbLink->connectEx($dbIni, true)) return false;
	
		set_time_limit(5*60);
		//	Удалим все таблицы базы данных
	
		$ini		= readIniFile("$backupFolder/config.ini");
		$ini[':db'] = $dbIni;
		if (!setIniValues($ini)){
			module('message:error', "Ошибка записи INI файла");
			return false;
		}
	
		ob_start();
		$site	= siteFolder();
		execPHP("index.php clearCacheCode $site");
	//	createCache();
		//	Перезагрузить кеш
	
		//	Восстановить данные
		self::restoreDeleteTables();
		$bOK = self::restoreDbData("$backupFolder/dbTableData.txt.bin");
		//	Восстановить изображения
		$images	= is_dir("$backupFolder/images");
		if ($images)
		{
			delTree(images);
			if (!copyFolder("$backupFolder/images", images)){
				module('message:error', "Ошибка копирования файлов " . images);
				$bOK = false;
			};
		}
		//	Если были ошибки, вывести их.
		$errors	= ob_get_clean();	
		if ($bOK) return $bOK;
	
		module('message:error', 'Ошибка восстановления');
		module('message:error', $errors);
		
		return $bOK;
	}
	static function restoreDeleteTables()
	{
		$db			= new dbRow();
		$dbName		= $db->dbName();
		$prefix		= $db->dbTablePrefix();
	
		$exclude	= array();
		$ex			= getCacheValue(':backupExcludeTables') or array();
		foreach($ex as $tableName){
			$tableName				= "$prefix$tableName";
			$exclude[$tableName]	= dbEncString($db, $tableName);
		}
		$ex			= implode(',', $exclude);
	
		$sql	= array();
		$sql[]	= "`Name` LIKE '$prefix%'";
		if ($ex)	$sql[]	= "`Name` NOT IN ($ex)";
		$sql	= implode(' AND ', $sql);
	
		$db->exec("SHOW TABLE STATUS FROM `$dbName` WHERE $sql");
		while($data = $db->next()){
			$db->execSQL("TRUNCATE `$data[Name]`");
		}
	}
	static function restoreDbData($fileName)
	{
		@$f = fopen($fileName, "r");
		if (!$f) return false;
	
		$bOK		= true;
		$db			= new dbRow();
		
		$tableName 	= '';
		$colsName	= array();
		$tableCols	= array();
	
		$bSkipTable	= true;
		$ex			= getCacheValue(':backupExcludeTables');
	
		$rowIndex	= 0;
		lockMessage();
		while($row = fgets($f, 5*1024*1024))
		{
			++$rowIndex;
			
			$row = explode("\t", rtrim($row));
			//	Skip empty rows
			if (!$row) continue;
			//	Table name
			if (count($row)==1 && $row[0][0]=='#')
			{
				$disableError	= false;
				$tableName		= trim($row[0], '#');
				$bSkipTable		= $ex[$tableName];
				$restoredTableName = $db->dbLink->dbTableName($tableName);
				$colsName	= array();
				$tableCols	= array();
	
				$row		= fgets($f, 1024*1024);
				$colsName	= explode("\t", strtolower(rtrim($row)));
				
				$db->exec("DESCRIBE `$restoredTableName`");
				while($data = $db->next()){
					$field	= $data['Field'];
					$tableCols[strtolower($field)] = $field;
				}
				unset($data);
				continue;
			}
			if (!$tableName) continue;
			if ($bSkipTable) continue;
	
			$data = array();
			foreach($row as $ndx => &$val)
			{
				$colName= $colsName[$ndx];
				if (!isset($tableCols[$colName])) continue;
				
				if ($val == 'zero') $val = 0;
				else
				if (preg_match('#^\d+$#', $val)){
					$val	= (int)$val;
				}else
				if ($val != 'NULL')
				{
					$val	= base64_decode($val);
					$val	= dbEncString($db, $val);
				}
				if ($colName == 'lastupdate'){
					$val = 'NULL';
				}
					
				$data[$colName] = $val;
				unset($val);
				unset($colName);
			}
			unset($row);
			if (!$data) continue;
	
			$res	= $db->insertRow($restoredTableName, $data);
			unset($res);
			
			$err = $db->error();
			if ($err && !$disableError)
			{
				$disableError	= true;
				$err = htmlspecialchars($err);
				echo "<div>$tableName строка $rowIndex: $err</div>";
	//			print_r($restoredTableName);
//				print_r($data);
				die;
				unset($err);
//				$bOK = false;
			}
			unset($data);
		}
		unlockMessage();
		fclose($f);
		return $bOK;
	}
}
?>