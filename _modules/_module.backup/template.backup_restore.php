<?
function backup_restore(&$db, $val, &$data)
{
	m('htaccess');
	
	$backupName		= $data[1];
	$backupFolder	= localHostPath.'/_backup/'.$backupName;
	$bHasBackup		= is_dir($backupFolder);
	@$note			= file_get_contents("$backupFolder/note.txt");
	@$passw			= file_get_contents("$backupFolder/password.bin");
	$passw2			= getValue('backupPassword');
	$bRestoreSuccess= false;
	
	module("page:display:!message", '');
	if ($bHasBackup &&
		testValue('doBackupRestore') &&
		testValue("backupRestoreYes") &&
		access('restore', "backup:$backupName:$passw2"))
	{
		if (backupRestore($backupFolder)){
			$bRestoreSuccess= true;
			module('message', 'Восстановление завершено');
			clearCache();
		}
	}

	module('script:ajaxForm');
	
	$images	= is_dir("$backupFolder/images")?' + изображения':'';
	if ($passw) $images .= ' + пароль';
	
	$class = testValue("backupRestoreYes")?' checked="checked"':'';
	if (testValue('doBackupRestore') && !testValue("backupRestoreYes"))
		m('message:error', 'Нажмите галочку для начала восстановления');
?>
{{page:title=Восстановление резервной копии}}
<? if (!$bHasBackup){
	module('message:error', "Нет резервной копии в папке \"<b>$backupFolder</b>\"");
	return module('display:message');
}?>
<h2>{$backupName}{$images}</h2>
<blockquote>
<pre>{$note}</pre>
</blockquote>
{{display:message}}
<? if ($bRestoreSuccess) return; ?>

<form action="<?= getURL("backup_$backupName")?>" method="post" class="admin ajaxForm">
<input type="hidden" name="doBackupRestore" />
<? if ($passw){ ?>
<?
//	Вывести настройки базы данных, если пароль введен и соединение с БД не установлено
if (access('restore', "backup:$backupName:$passw2")){
	$db	= new dbRow();
	if (!is_array($dbIni = getValue('dbIni'))) $dbIni = $db->getConfig();
	showDataBaseConfig($backupFolder, $dbIni);
}
//	Получить введенный пароль, для вывода в поле ввода
$url	= getURLEx('', "URL=backup_$backupName.htm");
?>
<p><input name="backupPassword" type="password" class="input password" size="16" value="{$passw2}" />  Введите пароль для восстановления</p>
Ссылка для экстренного восстановления <br />
<a href="{!$url}"><b>{$url}</b></a>
<? } ?>
<p><input name="backupRestoreYes" id="backupRestoreYes" type="checkbox" value="1"{!$class} /> <label for="backupRestoreYes">Восстановить сайт, все текущие данные будут уничтожены</label></p>
<p><input type="submit" value="Восстановить" class="button" /></p>
</form>
<? } ?>
<?
//	Восстановить базу из архива
function backupRestore($backupFolder)
{
	//	Определим константу, сообщающую, что идет восстановление
	define('restoreProcess', true);
	
	//	Получить конфигурационные данные БД или введенные пользователем, или из существующей конфигурации
	$db	= new dbRow();
	if (!is_array($dbIni = getValue('dbIni')))
		$dbIni = $db->getConfig();

		//	Проверить, что соединение с базой данных имеется
	if (!$db->dbLink->dbConnectEx($dbIni)) return false;


	$ini		= getCacheValue($ini);
	$ini[':db'] = $dbIni;
//	$ini[':']['globalRootURL']	= globalRootURL?globalRootURL:'/';
	setIniValues($ini);
	ob_start();
	//	Удалим все таблицы базы данных
//	restoreDeleteTables();
	$site	= getSiteURL();
	execPHP("index.php clearCacheCode $site");
	//	Перреинициализируем базу данных
//	compileFiles(localCacheFolder);
	//	Вызвать событие, по которому создатуся базы данных и другие настройки
//	event('config.end', $ini);

	//	Аосстановить данные
	$bOK = restoreDbData("$backupFolder/dbTableData.txt.bin");

	//	Восстановить изображения
	$images	= is_dir("$backupFolder/images");
	if ($images){
		delTree(images);
		$bOK &= copyFolder("$backupFolder/images", images);
	}
	//	Восстановить конфигурационный файл
	$configFileBackup	= "$backupFolder/config.ini";
	$configFileHost		= localConfigName;
	if (is_file($configFileBackup)) $bOK &= copy($configFileBackup, $configFileHost) !== false;

	//	Дополнить конфигурационный файл настройками базы данных
	$ini = readIniFile($configFileHost);
	$ini[':db'] = $dbIni;
	writeIniFile($configFileHost, $ini);

	//	Если были ошибки, вывести их.
	$errors	= ob_get_clean();	
	if (!$bOK){
		module('message:error', 'Ошибка восстановления');
		module('message:error', $errors);
	}
	
	return $bOK;
}
function restoreDeleteTables()
{
	$db			= new dbRow();
	$dbConfig	= $db->getConfig();
	$dbName		= $dbConfig['db'];
	$prefix		= $db->dbTablePrefix();

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
	$bOK		= true;
	$db			= new dbRow();
	
	$tableName 	= '';
	$colsName	= array();
	$tableCols	= array();
	
	while($row = fgets($f, 5*1024*1024))
	{
		$row = explode("\t", rtrim($row));
		//	Skip empty rows
		if (!$row) continue;
		//	Table name
		if (count($row)==1 && $row[0][0]=='#')
		{
			$tableName = trim($row[0], '#');
			$restoredTableName = $db->dbLink->dbTableName($tableName);
			$colsName	= array();
			$tableCols	= array();

			$row		= fgets($f, 1024*1024);
			$colsName	= explode("\t", strtolower(rtrim($row)));
			
			$db->exec("DESCRIBE `$restoredTableName`");
			while($data = $db->next()){
				$tableCols[strtolower($data['Field'])] = $data['Field'];
			}
			unset($data);
			continue;
		}
		if (!$tableName) continue;

		$data = array();
		foreach($row as $ndx => &$val)
		{
			$colName= $colsName[$ndx];
			if (!isset($tableCols[$colName])) continue;
			
			if ($val == 'zero') $val = 0;
			else
			if ($val != 'NULL' && strlen((int)$val) != strlen($val)){
				$val	= base64_decode($val);
				makeSQLValue($val);
			}
			$data[$colName] = $val;
		}
		unset($row);

		//	Delayed insert
		$db->insertRow($restoredTableName, $data);
		unset($data);
		
		$err = $db->error();
		if ($err){
			$err = htmlspecialchars($err);
			echo "<div>$err<div>";
			unset($err);
			$bOK = false;
		}

//		echo$tableName, ' ', mysql_error();
	}
	return $bOK;
}
?>
<? function showDataBaseConfig($backupFolder, $dbIni){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
<tr>
    <th colspan="2">Введите корректные параметры для базы данных</th>
</tr>
<tr>
    <td nowrap="nowrap">Адрес сервера БД</td>
    <td width="100%"><input type="text" name="dbIni[host]" class="input w100" value="{$dbIni[host]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Имя базы данных</td>
    <td width="100%"><input type="text" name="dbIni[db]" class="input w100" value="{$dbIni[db]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Логин</td>
    <td width="100%"><input type="text" name="dbIni[login]" class="input w100" value="{$dbIni[login]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Пароль</td>
    <td width="100%"><input type="text" name="dbIni[passw]" class="input w100" value="{$dbIni[passw]}" /></td>
</tr>
</table>
<? } ?>