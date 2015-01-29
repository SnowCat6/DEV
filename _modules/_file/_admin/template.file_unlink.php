<?
function file_unlink($val, $folders)
{
	$folders	= makeFilePath($folders);
	if (!canEditFile($folders)) return;
	
	$ix				= date('Ymd_His');
	$backupFolder	= images . "/file_undo/$ix";
	if (!is_array($folders)) $folders = array($folders);
	
	$siteFolder	= sitesBase . '/' . siteFolder();
	foreach($folders as $path)
	{
		$path2	= substr($path, strlen($siteFolder));
		copyFolder($path, "$backupFolder$path2");
		delTree($path);
	}
	
	$names	= implode(', ', $folders);
	logData("Delete folder $names", 'file', 
		array('undo' => array('action' => 'file:unlink_undo', 'data' => $backupFolder))
	);
}
?>
<?
//	+function file_unlink_undo
function file_unlink_undo($val, $backupFolder)
{
	if (!access('write', 'undo')) return;
	
	$siteFolder	= sitesBase . '/' . siteFolder();
	copyFolder($backupFolder, $siteFolder);
	return true;
}
?>