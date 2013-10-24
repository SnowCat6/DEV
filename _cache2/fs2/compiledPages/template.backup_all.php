<? function backup_all(&$db, $val, &$data){ ?><? $module_data = array(); $module_data[] = "Резервные копии"; moduleEx("page:title", $module_data); ?><?
	$backupFolder	= localHostPath.'/_backup';
	$backupPassword	= getValue('backupPassword');
	$deleteBackup	= getValue('deleteBackup');

	if (!access('read', 'backup')){
		module('message:error', 'Недостаточно прав доступа');
		return module('display:error');
	}
	if (is_array($deleteBackup))
	{
		$ndx = 0;
		foreach($deleteBackup as $name)
		{
			if (!is_dir("$backupFolder/$name")) continue;
			
			if (@$passw	= file_get_contents("$backupFolder/$name/password.bin"))
			{
				if ($passw != md5($backupPassword[$name])){
					module('message:error', "Неверный пароль для архива <b>$name</b>");
					continue;
				}
			}else
			if (!access('write', "backup:$name")){
				module('message:error', 'Недостаточно прав доступа');
				continue;
			}

			$ndx += 1;
			delTree("$backupFolder/$name");
		};
		if ($ndx) module('message', "$ndx архивных копий удалено");
	}
	
	$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
	$freeSpace		= "<p>Свободно: <b>$freeSpace Мб.</b></p>";
	
	$folders		= getDirs($backupFolder);
	if (!$folders){
		module('message:error', 'Резервные копии не найдены');
		echo $freeSpace;
		module('display:message');
		return;
	}

	module('script:ajaxLink');
	module('script:ajaxForm');
	$class = testValue('ajax')?' class="ajaxForm ajaxReload"':'';
?><? if(isset($freeSpace)) echo $freeSpace ?><? module("display:message"); ?>
<form action="<? module("getURL:backup_all"); ?>" method="post"<? if(isset($class)) echo $class ?>>
<?
	$folders		= array_reverse($folders);
	foreach($folders as $name => $backupFolder)
	{
		$url	= getURL("backup_$name");
		$time	= date('d.m.Y H:i:s', filemtime($backupFolder));
		$note	= file_get_contents("$backupFolder/note.txt");
		$images	= is_dir("$backupFolder/images")?' + изображения':'';
		if ($bHasPassword = is_file("$backupFolder/password.bin")){
			$images .= ' + пароль';
		}
		$class = $deleteBackup[$name]?' checked="checked"':'';
?>
<div><input type="checkbox" name="deleteBackup[<? if(isset($name)) echo htmlspecialchars($name) ?>]" value="<? if(isset($name)) echo htmlspecialchars($name) ?>"<? if(isset($class)) echo $class ?> />
<b><a href="<? if(isset($url)) echo $url ?>" id="ajax"><? if(isset($name)) echo htmlspecialchars($name) ?></a></b> <i><? if(isset($time)) echo htmlspecialchars($time) ?></i><? if(isset($images)) echo htmlspecialchars($images) ?></div>
<blockquote>
<pre><? if(isset($note)) echo htmlspecialchars($note) ?></pre>
<? if ($bHasPassword){ ?>
<p><input name="backupPassword[<? if(isset($name)) echo htmlspecialchars($name) ?>]" type="password" class="input password" size="16" value="<?= @htmlspecialchars($backupPassword[$name])?>" /> 
Введите пароль для удаления</p>
<? } ?>
</blockquote>
<?	} ?>
<p><input type="submit" class="button" value="Удалить выделенные копии" /></p>
</form>
<? } ?>
