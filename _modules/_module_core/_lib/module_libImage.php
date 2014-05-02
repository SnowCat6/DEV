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
		event('image.delete', $folder);
	}unlinkAutoFile($filePath);
	
	makeDir($folder);
	$bOK	=  copy($source, $filePath);
	fileMode($filePath);
	event('image.upload', $filePath);
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
	if ($w*$h < 1500*1500*3) return false;

	m("message:trace:error", "Big image size $path");
	return true;
}
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath='')
{
	if (isMaxFileSize($srcPath)) return false;

	return module('image:resizeImage', array(
		'srcPath'=>$srcPath,
		'w'=>$w, 'h'=>$h,
		'dstPath'=>$dstPath
	));
}
function checkResize($src, $dst, $iw, $ih, $w, $h){
	if ($src==$dst && $iw==$w && $ih==h) return false;
	if ($src!=$dst && is_file($dst)){
		@list($iw, $ih)=getimagesize($dst);
		if ($iw==$w && $ih==h) return false;
	}
	return true;
}
function  loadImage($src)
{
	list($file, $ext) = fileExtension($src);
	$img = NULL;
	switch(strtolower($ext)){
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
//	Получить список файлов по фильтру
function getFileList($dir, $filter, $isFiles=true){
	@$d=opendir($dir);
	$files = array();
	while((@$file=readdir($d))!=NULL){
		$f = "$dir/$file";
		if (!preg_match("#$filter#", $file)) continue;
		if ($isFiles){
			if (!is_file($f)) continue;
		}else{
			if ($file=='.' || $file=='..' || !is_dir($f)) continue;
		}
		$files[$file]=$f;
	}
	@closedir($d);
	ksort($files);
	return $files;
}
//	Удалить файл со всеми возможными сопровождающими данными
function unlinkAutoFile($path){
	moduleEx('image:unlinkAutoFile', $path);
}
function unlinkFile($path){
	moduleEx('image:unlink', $path);
}
//	Получить расширение файла
function fileExtension($path){
	$file = explode('.', $path);
	$ext = array_pop($file);
	return array(implode('.', $file), $ext);
}
//	
function displayThumbImage($src, $w, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;
	
	return module('image:displayThumbImage', array(
		'src'=>$src,
		'w'=>$w,
		'options'=>$options,
		'altText'=>$altText,
		'showFullUrl'=>$showFullUrl,
		'rel'=>$rel
	));
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function displayThumbImageMask($src, $maskFile, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;
	
	return module('image:displayThumbImageMask', array(
		'src'=>$src,
		'maskFile'=>$maskFile,
		'options'=>$options,
		'altText'=>$altText,
		'showFullUrl'=>$showFullUrl,
		'rel'=>$rel
	));
}
function displayImage($src, $options='', $altText='')
{
	@list($w, $h) = getimagesize($src);
	if (!$w || !$h) return false;

	$src 	= imagePath2local($src);
	$altText= htmlspecialchars($altText);
	$altText= " alt=\"$altText\" title=\"$altText\"";
	echo "<img src=\"$src\" width=\"$w\" height=\"$h\"$altText$options />";
	return true;
}
function showPopupImage($src, $showFullUrl, $ctx, $alt='', $rel='')
{
	module('script:lightbox');
	$rel 		= $rel?"lightbox[$rel]":'lightbox';
	$showFullUrl= imagePath2local($showFullUrl);
	echo "<a href=\"$showFullUrl\" class=\"zoom\" title=\"$alt\" target=\"image\" rel=\"$rel\">", $ctx, "<span></span></a>";
}
function imagePath2local($src){
	$src		= str_replace(globalRootURL.'/'.localRootPath.'/',	'', globalRootURL."/$src");
	$src		= str_replace('/'.localRootPath.'/', 				'', globalRootURL."/$src");
	return $src;
}
function clearThumb($folder){

	$files = getFileList($folder, '^thumb', false);
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getFileList($folder, '', false);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>