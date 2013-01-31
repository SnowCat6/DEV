<? function backup_all(&$db, $val, &$data){ ?>
<h1>Резервные копии</h1>
<?
	$backupFolder	= localHostPath.'/_backup';
	$backupPassword	= getValue('backupPassword');
	$deleteBackup	= getValue('deleteBackup');
	if (testValue('ajax')) setTemplate('ajax');

	if (is_array($deleteBackup))
	{
		$ndx = 0;
		foreach($deleteBackup as $name)
		{
			$n	= preg_replace('#([^\d-])#', '', $name);
			if (!is_dir("$backupFolder/$n")) continue;
			
			if (@$passw	= file_get_contents("$backupFolder/$n/password.bin"))
			{
				if ($passw != @md5($backupPassword[$name])){
					module('message:error', "Неверный пароль для архива <b>$name</b>");
					continue;
				}
			}

			$ndx += 1;
			delTree("$backupFolder/$n");
		};
		if ($ndx) module('message', "$ndx архивных копий удалено");
	}
	
	$freeSpace		= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
	$freeSpace		= "<p>Свободно места: <b>$freeSpace Мб.</b></p>";
	
	$folders		= getDirs($backupFolder);
	if (!$folders){
		module('message:error', 'Резервные копии не найдены');
		module('display:message');
		echo $freeSpace;
		return;
	}

	module('script:popupWindow');
	module('script:ajaxForm');
?>
{!$freeSpace}
{{display:message}}
<form action="{{getURL:backup_all}}" method="post" class="ajaxForm ajaxReload">
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
<p><input name="backupPassword[{$name}]" type="password" class="input" size="16" value="<?= @htmlspecialchars($backupPassword[$name])?>" /> 
Введите пароль для удаления</p>
<? } ?>
</blockquote>
<?	} ?>
<p><input type="submit" class="button" value="Удалить выделенные копии" /></p>
</form>
<? } ?>
