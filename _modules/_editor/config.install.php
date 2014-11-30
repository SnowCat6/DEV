<?
//	autoinstall CKEditor from system PHAR ZIP file
//	Search in zip files _editor folder and extract
if (extension_loaded("zip"))
{
	$baseDir= '_editor/';
	$nLen	= strlen($baseDir);
	
	$zip	= new ZipArchive();
	$files	= getFiles('./', 'zip$');
	foreach($files as $file)
	{
		if (!$zip->open($file)) continue;
		
		$extractFiles	= array();
		for($i = 0; $i < $zip->numFiles; $i++)
		{
			$entry = $zip->getNameIndex($i);
			if (strncmp($entry, $baseDir, $nLen)) continue;
			$extractFiles[]	= $entry;
		}
		$zip->extractTo('./', $extractFiles);
		$zip->close();
	}
}
?>