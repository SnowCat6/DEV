<?
//	Вывод и манипуляция изображениями на сайте
//	{{file:imge:doc$id=mask:design/mask.png;hasAdmin=true;attribute.style.class:color;adminMenu:$menu}}
function file_image(&$storeID, &$data)
{
	if (!$storeID) $storeID	= 'ini';

	if ($data['mask'])	return file_imageMask($storeID, $data);

	if ($data['width'] && $data['height']) $data['size'] = array($data['width'], $data['height']);
	else if ($data['width']) $data['size'] = $data['width'];
	if ($data['size'])	return file_imageSize($storeID, $data);

	//	Вернуть путь к файлу с обложки
	$files	= file_imageGet($storeID, $data);
	list($file,) = each($files);
	if ($file) displayImage($file, $data['property']);

	return $file;
}
//	+function file_imageGet
function file_imageGet(&$storeID, &$data)
{
	$file	= $data['src'];
	if ($file) return array($file);
	
	$uploadFolder			= makeFilePath($data['uploadFolder']);
	$data['uploadFolder']	= $uploadFolder;
	return getFiles($uploadFolder, '');
}
//	+file_imageSize
function file_imageSize(&$storeID, &$data)
{
	$files		= file_imageGet($storeID, $data);
	$property	= $data['property'];

	if ($data['hasAdmin'] && canEditFile($data['uploadFolder']))
		return module("file:imageSizeMenu:$storeID", $data);

	$menu	= $data['adminMenu'];
	beginAdmin($menu);
	
	$bOne				= $data['multi'] != 'true';
	$property['width']	= $data['size'];
	foreach($files as $path)
	{
		$property['src']	= $path;
		moduleEx('image:displayThumbImage', $property);
		if ($bOne) break;
	}
	endAdmin();
}
//	+file_imageMask
function file_imageMask(&$storeID, &$data)
{
	$files		= file_imageGet($storeID, $data);
	$property	= $data['property'];
	
	if ($data['hasAdmin'] && canEditFile($data['uploadFolder']))
		return module("file:imageMaskMenu:$storeID", $data);
	
	$menu = $data['adminMenu'];
	
	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImageMask',
		'content'	=> &$storage);
	//	Получить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);
	if (!is_array($storage)) $storage = array();

	$mask				= $data['mask'];
	$uploadFolder		= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);
	
	$property[':mask']	= $mask;
	$property[':offset']['top']	= (int)$storage[$uploadFolder][$mask];

	beginAdmin($menu);
	$bOne	= $data['multi'] != 'true';
	foreach($files as $path)
	{
		$property['src']	= $path;
		moduleEx('image:displayThumbImageMask', $property);
		if ($bOne) break;
	}
	endAdmin();
}

?>