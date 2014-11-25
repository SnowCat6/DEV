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
	@list($w,$h) = getimagesize($path);
	if (!$w || !$h) return true;
	if ($w*$h < 1800*1800) return false;

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
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath='')
{
	return module('image:resizeImage', array(
		'src'	=> $srcPath,
		'dst'	=> $dstPath,
		'w'		=> $w,
		'h'		=> $h
	));
}
//	
function displayThumbImage($src, $w, $options = '', $altText='', $showFullUrl='', $rel='')
{
	$property			= array();
	$property['src']	= $src;
	$property['width']	= $w;
	$property['alt']	= $altText;
	$property['title']	= $altText;
	$property['rel']	= $rel;
	$property[]			= $options;
	
	return moduleEx('image:displayThumbImage', $property);
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function displayThumbImageMask($src, $maskFile, $options='', $altText='', $showFullUrl='', $rel='', $offset='')
{
	$property			= array();
	$property['alt']	= $altText;
	$property['title']	= $altText;
	$property['rel']	= $rel;
	$property[]			= $options;
	$property['src']	= $src;
	$property[':mask']	= $maskFile;
	$property[':offset']= $offset;

	return moduleEx('image:displayThumbImageMask', $property);
}
function displayImage($src, $property = '', $altText = '')
{
	if (!is_array($property))
		$property = array($property);
		
	if ($altText){
		$property['alt']	= $altText;
		$property['title']	= $altText;
	}
	
	$property['src']	= $src;
	return moduleEx('image:display', $property);
}
function imagePath2local($src)
{
	$src		= str_replace(globalRootURL.'/'.localRootPath.'/',	'', globalRootURL."/$src");
//	$src		= str_replace(cacheRootPath.'/',					'', globalRootURL."/$src");
	$src		= str_replace('/'.localRootPath.'/', 				'', globalRootURL."/$src");
	return $src;
}
function clearThumb($folder){

	$files = getDirs($folder, '^thumb');
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getDirs($folder);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>