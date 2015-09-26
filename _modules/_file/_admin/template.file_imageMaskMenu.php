<?
/*************************/
//	+function file_imageMaskMenu
function file_imageMaskMenu(&$storeID, &$data)
{
	$files	= module("file:imageGet:$storeID", $data);

	$property	= $data['property'];
	if ($href = $property['href']) unset($property['href']);
	
	$menu	= $data['adminMenu'];
	if (!is_array($menu)) $menu = array();
	
	$menu[':type']	= $data['hasAdmin'];
	
	$uploadFolder	= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);
	$mask			= $data['mask'];

	$maskFile	= getSiteFile($mask);
	list($w, $h)= getimagesize($maskFile);
	$maskFile	= globalRootURL . imagePath2local($maskFile);

	$m	= makeQueryString(array(
		'storeID'		=> $storeID,
		'mask'			=> $mask,
		'uploadFolder'	=> $uploadFolder
	));
	$menu['Изображение']	= '';
	$menu['Кадрировать']		= array(
		'href'	=> getURL("file_imageMaskUpload", $m),
		'class'	=> 'adminImageMaskHandleEx',
		'title'	=> 'Выравнять по вертикали изображение для наилучшего вида'
	);

	$menu['Загрузить']	= array(
		'class'	=> 'adminImageMaskUploadEx',
		'rel'	=> json_encode(array('uploadFolder' => $uploadFolder)),
		'href'	=> getURL('#'),
		'title'	=> 'Загрузить изображение'
	);
	$menu['Удалить']	= array(
		'class'	=> 'adminImageMaskDeleteEx',
		'href'	=> getURL('#'),
		'title'	=> 'Удалить изображение'
	);
	
	
	if (count($files) == 0){
		$menu[':class']['noImage']	= 'noImage';
	}
	$menu[':class']['adminMaskArea']= 'adminMaskArea';
	$menu[':style']['width']	= $w . 'px';
	$menu[':style']['height']	= $h . 'px';
	
	$menu[':before']	= "<div class=\"adminMaskImage\">";
	if ($href){
		$p				= makeProperty($property);
		$menu[':after']	= "</div><a href=\"$href\" $p><img src=\"$maskFile\" class=\"adminMaskImageMask\" /></a>";
	}else{
		$menu[':after']	= "</div><img src=\"$maskFile\" class=\"adminMaskImageMask\" />";
	}

	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImageMask',
		'content'	=> &$storage);
	//	Получить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);
	if (!is_array($storage)) $storage = array();
	$offset	= (int)$storage[$uploadFolder][$mask] . 'px';

	beginAdmin($menu);
	$property['style']	= "top: $offset";
	foreach($files as $path)
	{
		list($iw, $ih)	= getimagesize($path);
		$r	= $h?$w/$h:0;
		$ir	= $ih?$iw/$ih:0;
		if ($r > $ir){
			$property['width']	= "100%";
			$property['height']	= "";
		}else{
			$property['width']	= "";
			$property['height']	= "100%";
		}
		
		if (isMaxFileSize($path))	$path	= 'design/siteBigImage.gif';
		$property['src']	= $path;
		module('image:display', $property);
	}
	endAdmin();

	m('script:jq');
	m('script:fileUpload');
	m('fileLoad', 'css/adminMask.css');
	m('fileLoad', 'script/jQuery.adminImageMaskEx.js');

	return $menu;
}
//	+function file_imageMaskUpload
function file_imageMaskUpload(&$val, &$data)
{
	setTemplate('');
	
	$storeID	= getValue('storeID');
	$mask		= getValue('mask');
	$uploadFolder	= getValue('uploadFolder');
	if (!canEditFile($uploadFolder)) return;

	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImageMask',
		'content'	=> &$storage);
	//	Получить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);
	if (!is_array($storage)) $storage = array();
	
	$storage[$uploadFolder][$mask]	= (int)getValue('top');
	event('storage.set', $ev);

	$data	= array('uploadFolder' => $uploadFolder);
	$files	= module("file:imageGet:$storeID", $data);
	foreach($files as $path){
		unlinkAutoFile($path);
	}
	clearCache();
}
?>