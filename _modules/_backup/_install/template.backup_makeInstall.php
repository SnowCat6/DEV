<?
function backup_makeInstall(&$db, $val, &$backupName)
{
	if (!getValue('backupInstall')) return;
	if (!access('write', 'backup'))	return;
	if (!extension_loaded("zip"))	return;
	
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
	$backupArchive	= "$backupFolder/$backupName.zip";

	$zip	= new ZipArchive();
	if ($zip->open($backupArchive, ZIPARCHIVE::CREATE) !== true) return;
	
	$exclude	= '\.git|\.scc|/_notes';
	backupAppendFolder($zip, '',	array(
		$exclude,
		'^' . sitesBase,
		'^' . globalCacheFolder,
		'^' . 'Templates',
		'^' . '\.htaccess'
		));
	
	backupAppendFolder($zip, $backupFolder, array($exclude, '/code'));
	backupAppendFolder($zip, localRootPath . '/' . modulesBase, $exclude);
	backupAppendFolder($zip, localRootPath, array(
		preg_quote(images, '#'),
		'/_', $exclude
	));
	
	$install	= 'install_restore.txt';
	$zip->addFile(getSiteFile($install), $install);

	$install2	= 'install.php';
	$zip->addFile(getSiteFile($install2), $install2);
	
	$zip->close();
	
	if (is_file($backupArchive))
	{
		$url2	= getURL() . $backupArchive;
		$url3	= getURL() . $install;
		$size	= round(filesize($backupArchive) / (1000*1000), 2);
	
		module('message', "<b><a href=\"$url2\" target=\_new\">Файл для установки сайта.</a></b> $size Мб." .
			"<div><a href=\"$url3\" target=\"new\">Инструкция по восстановлению.</a></div>");
	}else{
		module('message:error', 'Ошибка при создании архива.');
	}
}

function backupAppendFolder($zip, $folder, $exclude = '')
{
	if (is_array($exclude)) $exclude = implode('|', $exclude);
	backupZipAddDir($zip, $folder, $exclude);
}

function backupZipAddDir($zip, $path, $exclude = '')
{ 
	if ($exclude && preg_match("#($exclude)#", $path)) return;
    if ($path) $zip->addEmptyDir($path); 

    $files	= scanFolder($path); 
    foreach ($files as $file)
	{ 
		if ($exclude && preg_match("#($exclude)#", $path)) return;
        if (is_dir($file)) { 
			backupZipAddDir($zip, $file, $exclude);
        } else{ 
			if ($exclude && preg_match("#($exclude)#", $file)) continue;
            $zip->addFile($file); 
        } 
    } 
} 
?>