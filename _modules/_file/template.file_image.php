<?
//	Вывод и манипуляция изображениями на сайте
function file_image(&$storeID, &$data)
{
	if (!$storeID) $storeID	= 'ini';
	
	if ($data['mask'])	return file_imageMask($storeID, $data);

	if ($data['width'] && $data['height']) $data['size'] = array($data['width'], $data['height']);
	else if ($data['width']) $data['size'] = $data['width'];
	if ($data['size'])	return file_imageSize($storeID, $data);

	$files	= file_imageGet($storeID, $data);
	
	if ($data['hasAdmin'] && canEditFile($data['uploadFolder']))
		return module("file:imageMenu:$storeID", $data);
		
	$property	= $data['property'];
	foreach($files as $path)
	{
		$property['src']	= $path;
		moduleEx('image:display',	$property);
	}
}
//	+function file_imageGet
function file_imageGet(&$storeID, &$data)
{
	$file	= $data['src'];
	if ($file) return array($file);
	
	$uploadFolder			= makeFilePath($data['uploadFolder']);
	$data['uploadFolder']	= $uploadFolder;
	
	$bOne	= $data['multi'] != 'true';
	$files	= getFiles($uploadFolder, '');
	if (!$bOne)	return $files;
	
	list(, $file)	= each($files);
	return $file?array($file):array();
}
//	+file_imageSize
function file_imageSize(&$storeID, &$data)
{
	$files		= file_imageGet($storeID, $data);

	if ($data['hasAdmin'] && canEditFile($data['uploadFolder']))
		return module("file:imageSizeMenu:$storeID", $data);
	
	$property	= $data['property'];
	$menu		= $data['adminMenu'];
	$property['width']	= $data['size'];
	
	beginAdmin($menu);
	foreach($files as $path)
	{
		$property['src']	= $path;
		moduleEx('image:displayThumbImage', $property);
	}
	endAdmin();
}
//	+file_imageMask
function file_imageMask(&$storeID, &$data)
{
	$files		= file_imageGet($storeID, $data);

	if ($data['hasAdmin'] && canEditFile($data['uploadFolder']))
		return module("file:imageMaskMenu:$storeID", $data);
	
	$property	= $data['property'];
	$menu		= $data['adminMenu'];
	
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
	foreach($files as $path)
	{
		$property['src']	= $path;
		moduleEx('image:displayThumbImageMask', $property);
	}
	endAdmin();
}

?>