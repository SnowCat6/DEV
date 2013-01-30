<?
function backup_restore(&$db, $val, &$data)
{
	$backupName		= $data[1];
	$backupFolder	= localHostPath.'/_backup/'.$backupName;
	$bHasBackup		= is_dir($backupFolder);
	@$note	= file_get_contents("$backupFolder/note.txt");
	
	if (testValue('doBackupRestore')){
		if (testValue("backupRestoreYes")){
			module('message', 'Восстановление началось');
		}else{
			module('message:error', 'Нажмите галочку для начала восстановления');
		}
		if (testValue('ajax')) return;
	}

	module('script:ajaxForm');
	$class = testValue("backupRestoreYes")?' checked="checked"':'';
?>
<h1>Восстановление резервной копии</h1>
<? if (!$bHasBackup){
	module('message:error', "Нет резервной копии в папке \"<b>$backupFolder</b>\"");
	return module('display:message');
}?>
<h2>{$backupName}</h2>
<blockquote><pre>{$note}</pre></blockquote>
{{display:message}}
<form action="<?= getURL("backup_$backupName")?>" method="post" class="admin ajaxForm">
<input type="hidden" name="doBackupRestore" />
<p><input name="backupRestoreYes" id="backupRestoreYes" type="checkbox" value="1"{!$class} /> <label for="backupRestoreYes">Восстановить</label></p>
<div><input type="submit" value="Восстановить" class="button" /></div>
</form>
<? } ?>