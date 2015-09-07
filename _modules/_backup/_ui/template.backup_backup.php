<?
function backup_backup(&$db, $val, $options)
{
	if (!access('write', 'backup')) return;
	
	m('backup:exclude', 'stat_tbl');

	$backupName	= $options['name'];
	$backupPassw= $options['passw'];

	$backupFolder	= localRootPath.'/_backup/'.$backupName;

	$freeSpace	= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
	$freeSpace	= "<p>Свободно: <b>$freeSpace Мб.</b></p>";
	
	if (!dbBackup::makeBackup($backupFolder, $options))
	{
		module('message:error', "Ошибка архивации \"<b>$backupName</b>\", $freeSpace");
		return false;
	}

	if ($backupPassw)
	{
		$url 	= getURLEx() . "index.php?URL=backup_$backupName.htm";
		
		module('message',
			"Архивация завершена \"<b>$backupName</b>\", $freeSpace<br />".
			"Ссылка для экстренного восстановления <div>".
			"<a href=\"$url\" target=\"new\"><b>$url</b></a></div>");
				
		module("backup:makeInstall", $backupName);
	}else{
		module('message', "Архивация завершена \"<b>$backupName</b>\", $freeSpace");
	}
	
	return $backupFolder;
};
?>