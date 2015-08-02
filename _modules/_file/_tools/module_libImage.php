<?
if (!extension_loaded('gd'))	dl('gd.so') || dl('gd2.dll');
if (function_exists('imagecreatetruecolor')) 
	define('gd2', true);

//////////////////////////////////////////////////////
//	Различные общие функции
/////////////////////////////////////////////////////
//	Копировать файл в в указаную папку
//	Если путь содерит /Title/ то удалить все содержимое папки назначения
//	Так-же удалить все автосгенерированые миниизображения файла
//	Присвоить коректные права доступа к файлу
function copy2folder($source, $filePath)
{
	$folder	= dirname($filePath);
	
	if (isFileTitle($filePath)){
		delTree($folder);
		event('file.delete', $folder);
	}
	unlinkAutoFile($filePath);
	
	makeDir($folder);
	$bOK	=  copy($source, $filePath);
	copy("$source.shtml", "$filePath.shtml");
	
	fileMode($filePath);
	event('file.upload', $filePath);
	
	return $bOK;
}

function isFileTitle($filePath){
	return strpos($filePath, '/Title/') > 0;
}

function isMaxFileSize($path)
{
	if (!$path) return true;
	m("message:trace", "Read image $path");

	if (!defined('gd2')) return true;
	@list($w, $h) = getimagesize($path);
	if (!$w || !$h) return true;
/*
	$percent	= ($w*$h) / (1800*1800);
	if ($percent <= 1) return false;
	
	$w	= round($w/$percent);
	$h	= round($h/$percent);
*/
	$ramMax	= (int)ini_get('memory_limit') * 1000*1024;
	$ramUse	= memory_get_usage();
	$ramNow	= $ramMax - $ramUse - 2*1000*1024;
	if ($ramNow - ($w*$h*4) > 0){
//		echo $ramNow - ($w*$h*4);
		return false;
	}

	m("message:trace:error", "Big image size $path");
	return true;
}
//	Удалить файл со всеми возможными сопровождающими данными
function unlinkAutoFile($path){
	moduleEx('image:unlinkAutoFile', $path);
}
function unlinkFile($path){
	moduleEx('image:unlink', $path);
}
function imagePath2local($src)
{
	$p	= array(
		cacheRootPath,	//	Файлы на диске в кеше
		localRootURL,	//	Файл ссылка URL
		localRootPath	//	Файл на диске
	);
	foreach($p as $path){
		$nLen	= strlen($path);
		if (strncmp($src, $path, $nLen) == 0)
			return substr($src, $nLen);
	}
	return $src;
}
function clearThumb($folder){

	$files = getDirs($folder, '^thumb');
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getDirs($folder);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>