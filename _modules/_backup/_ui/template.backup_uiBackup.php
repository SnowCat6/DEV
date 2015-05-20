<? function backup_uiBackup($db, $val, $data)
{
	if (!access('write', 'backup')) return;
	
	$backupName		= date('Y-m-d-H-i', time());
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
	$note			= getValue('backupNote');
	$passw			= getValue('backupPassword');
	
	if (testValue('backupNote'))
	{
		$options				= array();
		$options['name']		= $backupName;
		$options['note']		= getValue('backupNote');
		$options['passw']		= getValue('backupPassword');
		$options['backupImages']= getValue('backupImages');

		$bOK	= module("backup:backup", $options);
	}
	
	$freeSpace	= number_format(round(disk_free_space(globalRootPath)/1024/1024), 0);
?>
{{page:title=Архивация сайта}}
<p>Свободно: <b>{$freeSpace} Мб.</b></p>
{{display:message}}
<? } ?>
