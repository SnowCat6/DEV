<?
function backup_restore(&$db, $val, &$data)
{
	systemHtaccess::htaccessMake();
	
	$backupName		= $data[1];
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
	$bHasBackup		= is_dir($backupFolder);
	$note			= file_get_contents("$backupFolder/note.txt");
	$passw			= file_get_contents("$backupFolder/password.bin");
	$passw2			= getValue('backupPassword');
	$bRestoreSuccess= false;
	
	module("page:display:!message", '');
	if ($bHasBackup &&
		testValue('doBackupRestore') &&
		testValue("backupRestoreYes"))
	{
		if (access('restore', "backup:$backupName:$passw2"))
		{
			$dbIni	= getValue('dbIni');
			if (is_array($dbIni)){
				$dbIni	= dbIni::get($dbIni);
			}else{
				$dbIni = $db->getConfig();
			}

			if (dbRestore::makeRestore($backupFolder, $dbIni))
			{
				$bRestoreSuccess= true;
				$site			= siteFolder();
				execPHP("index.php clearCache $site");
				module('message', 'Восстановление завершено');
			};
		}else{
			module('message:error', 'Неверный пароль');
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

<form action="<?= getURL("backup_$backupName")?>" method="post" class="admin">
<input type="hidden" name="doBackupRestore" />
<? if ($passw){ ?>
<?
//	Вывести настройки базы данных, если пароль введен и соединение с БД не установлено
if (access('restore', "backup:$backupName:$passw2"))
{
	$db	= new dbRow();
	if (!is_array($dbIni = getValue('dbIni'))) $dbIni = $db->getConfig();
	showDataBaseConfig($backupFolder, $dbIni);
}
//	Получить введенный пароль, для вывода в поле ввода
$url	= getURLEx('') . "index.php?URL=backup_$backupName.htm";
?>
<p><input name="backupPassword" type="password" class="input password" size="16" value="{$passw2}" />  Введите пароль для восстановления</p>
    <p>Ссылка для экстренного восстановления.<br />
    <a href="{$url}" _target="new"><b>{$url}</b></a>
</p>
<? if (is_file($zipArchive = "$backupFolder/$backupName.zip")){
	$size	= round(filesize($zipArchive) / (1000*1000), 2);
?>
<p>
    Файл установки сайта. <a href="{{url}}install_restore.txt" target="new">Инструкция по восстановлению.</a><br>
    <a href="{{url}}{$zipArchive}" target="new"><b>{$zipArchive}</b></a> {$size} Мб.
</p>
<? } ?>
<? } ?>
<p><input name="backupRestoreYes" id="backupRestoreYes" type="checkbox" value="1"{!$class} /> <label for="backupRestoreYes">Восстановить сайт, все текущие данные будут уничтожены</label></p>
<p><input type="submit" value="Восстановить" class="button" /></p>
</form>
<? } ?>

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