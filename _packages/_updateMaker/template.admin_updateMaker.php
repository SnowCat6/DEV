<?
//	+function admin_updateMakerTools
function admin_updateMakerTools($val, &$menu)
{
	if (!hasAccessRole('developer')) return;

	$menu['Создать обновление#ajax']	= getURL('admin_updateMaker');
}
?>

<?
function admin_updateMaker($val, $data)
{
	if (!hasAccessRole('developer')) return;

	$update		= new cmsUpdate();
	m('page:title', 'Создание пакета обновления');

	$builds	= explode(',', 'alpha,beta,stable');
	
	$data	= getValue('updateMake');
	if (!is_array($data))
	{
		$data	= array(
			'build' 	=> $builds[0],
			'version'	=> getCacheValue('DEV_CMS_VERSION')
		);
	}else{
	}
	
  if (getValue('doUpdateMake')){
		file_put_contents(serverUpdateFolder . "dev_cms_${data[build]}-${data[version]}.zip.txt", $data['note']);
		makeUpdateFile($data);
	}else{
		$note = file_get_contents(serverUpdateFolder . "dev_cms_${data[build]}-${data[version]}.zip.txt");
		if (!$note) $note = "CMS version ${data[build]} ${data[version]}\r\n";
		$data['note']	= $note;
	}
?>

<form action="{{url:#}}" method="post">

<p class="builds">
Тип обновления:<br>
<input type="text" name="updateMake[build]" class="input" value="{$data[build]}" />

<? foreach($builds as $build){ ?>
	<a href="#">{$build}</a>
<? } ?>

</p>

<p>
Номер обновления:<br>
<input type="text" name="updateMake[version]" class="input" value="{$data[version]}"  />
</p>

<input type="submit" class="button" value="Обновить" />
<input type="submit" class="button" name="doUpdateMake" value="Создать обновление" />

<h2>Описание обновления</h2>
<textarea name="updateMake[note]" class="input w100" rows="15">{$data[note]}</textarea>

</form>

<module:script:jq />
<script>
$(function()
{
	$(".builds a").click(function()
	{
		$(".builds input").val($(this).text());
		return false;
	});
});
</script>

<? } ?>


<?
function makeUpdateFile($buildInfo)
{
	$build	= $buildInfo['build'];
	$version= $buildInfo['version'];
	$note	= $buildInfo['note'];
	
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
	
	file_put_contents(serverUpdateFolder . "dev_cms_${build}-${version}.zip.txt", $note);
	
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