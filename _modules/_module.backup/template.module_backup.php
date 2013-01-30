<?
function module_backup($fn, &$data){
	//	База данных
	$db 		= new dbRow('backup_tbl', 'backup_id');
	$db->images = images.'/backup';
	$db->url 	= 'backup';
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("backup_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
?>