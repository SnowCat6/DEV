<?
//	autoinstall CKEditor from system PHAR ZIP file
//	Search in zip files _editor folder and extract to system folder
if (!extension_loaded("zip")) return;

$baseDir= '_editor/';
//	If folder exists, do not extract any
	if (is_dir($baseDir)) return;

//	Length of folder name for comparsion
$nLen	= strlen($baseDir);
//	Open add ZIP files from ROOT folder
$zip	= new ZipArchive();
$files	= getFiles('./', 'zip$');
foreach($files as $file)
{
	if (!$zip->open($file)) continue;
	//	Find files in archive
	$extractFiles	= array();
	for($i = 0; $i < $zip->numFiles; $i++)
	{
		$entry = $zip->getNameIndex($i);
		if (strncmp($entry, $baseDir, $nLen)) continue;
		//	Append to extract array
		$extractFiles[]	= $entry;
	}
	//	Extract files to ROOT folder
	if ($extractFiles) $zip->extractTo('./', $extractFiles);
	//	CLode archive
	$zip->close();
}
?>