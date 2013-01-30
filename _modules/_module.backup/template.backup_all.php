<? function backup_all(&$db, $val, &$data){ ?>
<h1>Резервные копии</h1>
<?
	$backupFolder	= localHostPath.'/_backup';
	if (testValue('ajax')) setTemplate('ajax');
	
	$deleteBackups = getValue('deleteBackup');
	if (is_array($deleteBackups))
	{
		$n = 0;
		foreach($deleteBackups as $name){
			$name = preg_replace('#([^\d-])#', '', $name);
			if (is_dir("$backupFolder/$name")) $n += 1;
			delTree("$backupFolder/$name");
		};
		module('message', "$n архивных копий удалено");
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
	$folders = array_reverse($folders);
	foreach($folders as $name => $path){
		$name	= htmlspecialchars($name);
		$url	= getURL("backup_$name");
		$time	= date('d.m.Y H:i:s', filemtime($path));
		@$note	= file_get_contents("$path/note.txt");
		$images	= is_dir("$path/images")?' + картинки':'';
?>
<div><input type="checkbox" name="deleteBackup[]" value="{$name}" /><b><a href="{!$url}" id="ajax">{$name}</a></b> <i>{$time}</i>{$images}</div>
<blockquote><pre>{$note}</pre></blockquote>
<?	} ?>
<input type="submit" class="button" value="Удалить выделенные копии" />
</form>
<? } ?>
