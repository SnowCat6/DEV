<?
function module_backup($fn, &$data)
{
	noCache();
	//	База данных
	$db 		= new dbRow();
	$db->url 	= 'backup';
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("backup_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function backup_access($db, &$val, &$data)
{
	switch($val){
	case 'restore':
		$backupName 	= $data[1];
		if (!$backupName) break;
		
		$backupFolder	= localHostPath."/_backup/$backupName";
		if (!is_dir($backupFolder)) return false;

		@$passw			= file_get_contents("$backupFolder/password.bin");
		if ($passw){
			//	Если хеши совпадают, то все нормально
			return md5($data[2]) == $passw;
		}
		break;
	}
	return hasAccessRole('backup');
}
?>