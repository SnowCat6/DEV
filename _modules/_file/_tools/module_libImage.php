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
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath='')
{
	if (isMaxFileSize($srcPath)) return false;

	return module('image:resizeImage', array(
		'src'	=> $srcPath,
		'dst'	=> $dstPath,
		'w'		=> $w,
		'h'		=> $h
	));
}
function checkResize($src, $dst, $iw, $ih, $w, $h)
{
	if ($src==$dst && $iw==$w && $ih==h) return false;
	
	if ($src!=$dst && is_file($dst)){
		@list($iw, $ih)=getimagesize($dst);
		if ($iw==$w && $ih==h) return false;
	}
	return true;
}
function  loadImage($src)
{
	$img = NULL;
	list($file, $ext) = fileExtension($src);
	switch(strtolower($ext))
	{
	case 'jpg':	@$img = imagecreatefromjpeg($src);	break;
	case 'png':	@$img = imagecreatefrompng($src);	break;
	case 'gif':	@$img = imagecreatefromgif($src);	break;
	}

	if (!$img) @$img = imagecreatefromjpeg($src);
	if (!$img) @$img = imagecreatefrompng($src);
	if (!$img) @$img = imagecreatefromgif($src);

	return $img;
}
function createFileDir($path){
	$dir='';
	$path=explode('/',str_replace('\\', '/', $path));
	while(list(,$name)=each($path))	@mkdir($dir.="$name/");
}
//	Удалить файл со всеми возможными сопровождающими данными
function unlinkAutoFile($path){
	moduleEx('image:unlinkAutoFile', $path);
}
function unlinkFile($path){
	moduleEx('image:unlink', $path);
}
//	Получить расширение файла
function fileExtension($path)
{
	$file = explode('.', $path);
	$ext = array_pop($file);
	return array(implode('.', $file), $ext);
}
//	
function displayThumbImage($src, $w, $options = '', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;
	
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
	if (isMaxFileSize($src)) return false;
	
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
function displayImage($src, $options='', $altText='')
{
	@list($w, $h) = getimagesize($src);
	if (!$w || !$h) return false;
	
	$property			= array();
	$property['alt']	= $altText;
	$property['title']	= $altText;
	$property[]			= $options;

	$property['src']	= imagePath2local($src);
	$property['width']	= $w;
	$property['height']	= $h;

	if ($href = $data['href'])
	{
		unset($data['href']);
		$href		= htmlspecialchars($href);
		$property	= makeProperty($data);
		echo "<a href=\"$href\"><img $property /></a>";
	}else{
		$property	= makeProperty($property);
		echo "<img $property />";
	}
	return $property['src'];
}
function imagePath2local($src){
	$src		= str_replace(globalRootURL.'/'.localRootPath.'/',	'', globalRootURL."/$src");
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