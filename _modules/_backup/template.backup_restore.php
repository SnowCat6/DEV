<?
function backup_restore(&$db, $val, &$data)
{
	m('htaccess');
	
	$backupName		= $data[1];
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
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
			$site	= siteFolder();
			execPHP("index.php clearCache $site");
			module('message', 'Восстановление завершено');
		};
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
	if (!$db->dbLink->connectEx($dbIni)) return false;
	
	set_time_limit(0);
	//	Удалим все таблицы базы данных

	$ini		= readIniFile("$backupFolder/config.ini");
	$ini[':db'] = $dbIni;
	$ini[':']['useCache']	= 1;
	setIniValues($ini);
	
	restoreDeleteTables();

	ob_start();
	$site	= siteFolder();
	execPHP("index.php clearCacheCode $site");
	//	Перезагрузить кеш
//	createCache();

	//	Аосстановить данные
	$bOK = restoreDbData("$backupFolder/dbTableData.txt.bin");

	//	Восстановить изображения
	$images	= is_dir("$backupFolder/images");
	if ($images){
		delTree(images);
		$bOK &= copyFolder("$backupFolder/images", images);
	}
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
	$dbName		= $db->dbName();
	$prefix		= $db->dbTablePrefix();

	$db->exec("SHOW TABLE STATUS FROM `$dbName` WHERE `Name` LIKE '$prefix%'");
	while($data = $db->next()){
		$db->execSQL("TRUNCATE `$data[Name]`");
//		$db->execSQL("DROP TABLE `$data[Name]`");
	}
}
function restoreDbData($fileName)
{
	@$f = fopen($fileName, "r");
	if (!$f) return false;
	
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
			$tableName	= trim($row[0], '#');
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

		$data = array();
		foreach($row as $ndx => &$val)
		{
			$colName= $colsName[$ndx];
			if (!isset($tableCols[$colName])) continue;
			
			if ($val == 'zero') $val = 0;
			else
			if ($val != 'NULL' && strlen((int)$val) != strlen($val)){
				$val	= base64_decode($val);
				$val	= dbEncString($db, $val);
			}
			$data[$colName] = $val;
			unset($val);
			unset($colName);
		}
		unset($row);

		$res	= $db->insertRow($restoredTableName, $data);
		unset($data);
		unset($res);
/*		
		$err = $db->error();
		if ($err){
			$err = htmlspecialchars($err);
			echo "<div>$err<div>";
			print_r($restoredTableName);
			print_r($data);
			die;
			unset($err);
			$bOK = false;
		}
*/
	}
	fclose($f);
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
<tr>
  <td nowrap="nowrap">Префикс таблиц</td>
  <td><input type="text" name="dbIni[prefix]" class="input w100" value="{$dbIni[prefix]}" /></td>
</tr>
</table>
<? } ?>