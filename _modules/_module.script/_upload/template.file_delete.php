<? function file_delete($val, &$data)
{
	$file	= $data[1];
	if (!canEditFile($file)) return;
	if (!is_file($file)) return;
	
	$fileName	= basename($file);
	
	if (testValue('deleteYes')){
		unlinkFile($file);
		m('message', "Файл удален: $fileName");
		module('display:message');
	}
	if (testValue('delete')){
		$url	= getURL("file_images_delete/$file", 'deleteYes');
		m('message', "Удплить файл: <a href=\"$url\">$fileName?</a>");
		module('display:message');
	}
}
?>