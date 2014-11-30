<?
function backup_makeInstall(&$db, $val, &$backupName)
{
	if (!access('write', 'backup'))	return;
	if (!extension_loaded("zip"))	return;
	
	$backupFolder	= localRootPath.'/_backup/'.$backupName;
	$backupArchive	= "$backupFolder/$backupName.zip";

	$zip	= new ZipArchive();
	if ($zip->open($backupArchive, ZIPARCHIVE::CREATE) !== true) return;
	
	$exclude	= '\.git|\.scc|/_notes';
	backupAppendFolder($zip, '',	array(
		$exclude,
		sitesBase,
		globalCacheFolder,
		'Templates',
		'\.htaccess'
		));
	
	backupAppendFolder($zip, $backupFolder, array($exclude, '/code'));
	backupAppendFolder($zip, localRootPath . '/' . modulesBase, $exclude);
	backupAppendFolder($zip, localRootPath, array(
		preg_quote(images, '#'),
		'/_', $exclude
	));
	
	$restoreHelp	= getSiteFile('install_restore.txt');
	if ($restoreHelp) $zip->addFromString('install_restore.txt', file_get_contents($restoreHelp));
	
	$zip->close();

	$url 	= getURLEx('', "URL=backup_$backupName.htm");
	$url2	= getURL('') . $backupArchive;
	$url3	= getURL('') . 'install_restore.txt';

	module('message', "<b><a href=\"$url2\" target=\_new\">Файл для установки сайта.</a></b>" .
		"<div><a href=\"$url3\" target=\"new\">Инстукция восстановления.</a></div>");
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