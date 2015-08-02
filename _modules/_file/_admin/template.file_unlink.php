<?
//	+function file_unlink
function file_unlink($val, $folders)
{
	$folders	= makeFilePath($folders);
	if (!canEditFile($folders)) return;
	
	$ix				= date('Ymd_His');
	$backupFolder	= "file_undo/$ix";
	if (!is_array($folders)) $folders = array($folders);
	
	$backupFolders	= array();
	$siteFolder		= sitesBase . '/' . siteFolder();
	foreach($folders as $path)
	{
		$path2	= substr($path, strlen($siteFolder));
		if (!$path2) continue;

		$dest				= images . "/$backupFolder$path2";
		$backupFolders[]	= $path2;

		if (is_dir($path)){
			copyFolder($path, $dest);
		}else{
			makeDir(dirname($dest));
			rename($path, $dest);
		}
		event('file.delete', $path);
	
		if (is_file($path))
		{
			rename("$path.shtml", "$dest.shtml");	//	Удалить комментарий к файлу
			unlinkAutoFile($path);
		}
	}
	
	$names	= implode(', ', $backupFolders);
	addUndo("Delete $names", 'file', 
		array(
		'action'=> 'file:unlink_undo',
		'clean'	=> 'file:unlink_undoClean',
		'data'	=> array(
			'backup'	=> $backupFolder,
			'folders'	=> $backupFolders
		))
	);
	return true;
}

//	+function file_unlink_undo
function file_unlink_undo($val, $folders)
{
	if (!access('write', 'undo')) return;
	
	$siteFolder		= sitesBase . '/' . siteFolder();
	$backupFolder	= $folders['backup'];
	$backupFolders	= $folders['folders'];
	if (!$folders) return;
	
	m("file:unlink", $backupFolders);
	
	foreach($backupFolders as $path2)
	{
		$path	= images . "/$backupFolder$path2";
		$dest	= $siteFolder . $path2;

		if (is_file($path)){
			copy2folder($path, $dest);
		}else{
			delTree($dest);
			copyFolder($path, $dest);
			event('file.upload', $path);
		}
	}
	
	return true;
}

//	Clean backup files on delete
//	+function file_unlink_undoClean
function file_unlink_undoClean($val, $folders)
{
	if (!access('write', 'undo')) return;

	$siteFolder		= sitesBase . '/' . siteFolder();
	$backupFolder	= $folders['backup'];

	$path	= images . "/$backupFolder";
	delTree($path);
	unlink(dirname($path));
}
?>