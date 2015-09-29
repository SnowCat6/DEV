<?
function admin_updateMaker($val, $data)
{
	if (!hasAccessRole('developer')) return;

	m('page:title', 'Создание пакета обновления');
	
	$data	= getValue('updateMake');
	if (!is_array($data))
	{
		$data = array(
			'build' => 'stable',
			'version' => getCacheValue('DEV_CMS_VERSION')
		);
	}else{
		makeUpdateFile($data['build'], $data['version']);
	}
?>

<form action="{{url:#}}" method="post">

<p>
Тип обновления:<br>
<input type="text" name="updateMake[build]" class="input" value="{$data[build]}" />
</p>

<p>
Номер обновления:<br>
<input type="text" name="updateMake[version]" class="input" value="{$data[version]}"  />
</p>

<input type="submit" class="button" value="Создать обновление" />

</form>

<? } ?>


<?
function makeUpdateFile($build, $version)
{
	$update		= new cmsUpdate();
	$fileName	= serverUpdateFolder . "dev_cms_${build}-${version}.zip";
	makeDir(serverUpdateFolder);
	unlink($fileName);
	
	$zip	= new ZipArchive();
	if ($zip->open($fileName, ZIPARCHIVE::CREATE) !== true) return;

	$exclude	= '\.git|\.scc|/_notes';
	backupAppendFolder($zip, '' . modulesBase,		$exclude);
	backupAppendFolder($zip, '' . templatesBase,	$exclude);
	backupAppendFolder($zip, '' . '_packages',		$exclude);
	backupAppendFolder($zip, '' . '_editor',		$exclude);

	$files 	= getFiles('', '^[^\.][^/]+$');
	foreach($files as $name => $filePath){
		$zip->addFile($name, $filePath);
	}
	$zip->close();
	
	$md5	= md5_file($fileName);
	file_put_contents("$fileName.md5", $md5);
	
	messageBox("Файл обновления создан");
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