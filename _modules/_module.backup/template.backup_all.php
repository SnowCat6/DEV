<? function backup_all(&$db, $val, &$data){ ?>
<h1>Резервные копии</h1>
<?
	$backupFolder	= localHostPath.'/_backup';
	$backupPassword	= getValue('backupPassword');
	$deleteBackup	= getValue('deleteBackup');
	if (testValue('ajax')) setTemplate('ajax');

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
				if ($passw != @md5($backupPassword[$name])){
					module('message:error', "Неверный пароль для архива <b>$name</b>");
					continue;
				}
			}else
			if (!access('write', "backup:$n")){
				module('message:error', 'Недостаточно прав доступа');
				continue;
			}

			$ndx += 1;
			delTree("$backupFolder/$n");
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

	module('script:popupWindow');
	module('script:ajaxForm');
	$class = testValue('ajax')?' class="ajaxForm ajaxReload"':'';
?>
{!$freeSpace}
{{display:message}}
<form action="{{getURL:backup_all}}" method="post"{!$class}>
<?
	$folders		= array_reverse($folders);
	foreach($folders as $name => $path)
	{
		$url	= getURL("backup_$name");
		$time	= date('d.m.Y H:i:s', filemtime($path));
		@$note	= file_get_contents("$path/note.txt");
		$images	= is_dir("$path/images")?' + изображения':'';
		if ($bHasPassword = is_file("$path/password.bin")){
			$images .= ' + пароль';
		}
		$class = @$deleteBackup[$name]?' checked="checked"':'';
?>
<div><input type="checkbox" name="deleteBackup[{$name}]" value="{$name}"{!$class} />
<b><a href="{!$url}" id="ajax">{$name}</a></b> <i>{$time}</i>{$images}</div>
<blockquote>
<pre>{$note}</pre>
<? if ($bHasPassword){ ?>
<p><input name="backupPassword[{$name}]" type="password" class="input password" size="16" value="<?= @htmlspecialchars($backupPassword[$name])?>" /> 
Введите пароль для удаления</p>
<? } ?>
</blockquote>
<?	} ?>
<p><input type="submit" class="button" value="Удалить выделенные копии" /></p>
</form>
<? } ?>
