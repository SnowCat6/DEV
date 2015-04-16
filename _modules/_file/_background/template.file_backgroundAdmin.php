<?
//	+function file_backgroundTools
function file_backgroundTools($val, &$data)
{
	if (!hasAccessRole('admin,developer')) return;

	global $_CONFIG;
	$names	= $_CONFIG['background:'];
	if (!$names) return;

	foreach($names as $name => $file){
		$data["Изменить \"$name\"#ajax"]	= getURL('admin_background', array('name' => $name));
	}
}
function file_backgroundAdmin($val, &$data)
{
	if (!hasAccessRole('admin,developer')) return;
	
	$name	= getValue('name');
	if (!$name) return;

	$ini	= getCache('background', 'ini');
	if (!is_array($ini)) $ini	= array();

	$folder	= images."/background/$name";
	
	if (getValue('backgroundDelete')){
		delTree($folder);
		$ini[$name]	= NULL;
		setCache('background', $ini, 'ini');
	}
	
	$tmpFile	= $_FILES['backgrooundFile']['tmp_name'];
	if ($tmpFile){
		delTree($folder);
		$fileName	= $_FILES['backgrooundFile']['name'];
		$fileName	= module('translit', $fileName);
		$file		= "$folder/$fileName";
		copy2folder($tmpFile, $file);

		$ini[$name]	= $file;
		setCache('background', $ini, 'ini');
	}

	$files	= getFiles($folder);
	list(, $file)	= each($files);
?>
{{page:title=Фоновое изображение $name}}
<form method="post" enctype="multipart/form-data" action="{{url:#=name:$name}}">
<? if ($file){ ?>
<p>
    <label><input type="checkbox" name="backgroundDelete"/>Удалить</label> <a href="{$file}" target="_blank">{$file}</a>
</p>
<? } ?>
<p><input type="file" title="Загрузить" name="backgrooundFile" /></p>
<input type="submit" value="Выполнить" class="button" />
</form>
<? } ?>
