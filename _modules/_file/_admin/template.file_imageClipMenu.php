<?
/*************************/
//	+function file_imageClipMenu
function file_imageClipMenu(&$storeID, &$data)
{
	$clip		= $data['clip'];
	list($w, $h)= is_array($clip)?$clip:explode('x', $clip);

	$files		= module("file:imageGet:$storeID", $data);

	$property	= $data['property'];
	if ($href = $property['href']) unset($property['href']);
	
	$menu	= $data['adminMenu'];
	if (!is_array($menu)) $menu = array();
	
	$menu[':type']	= $data['hasAdmin'];
	
	$uploadFolder	= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);
	
	$m	= makeQueryString(array(
		'storeID'		=> $storeID,
		'clip'			=> $w.'x'.$h,
		'uploadFolder'	=> $uploadFolder
	));
	$menu[]		= '';
	$menu['Кадрировать']		= array(
		'href'	=> getURL("file_imageClipUpload", $m),
		'class'	=> 'adminImageClipHandleEx',
		'title'	=> 'Выравнять по вертикали изображение для наилучшего вида'
	);

	$menu['Загрузить']	= array(
		'class'	=> 'adminImageClipUploadEx',
		'rel'	=> json_encode(array('uploadFolder' => $uploadFolder)),
		'href'	=> getURL('#'),
		'title'	=> 'Загрузить изображение'
	);
	
	
	if (count($files) == 0){
		$menu[':class']['noImage']	= 'noImage';
	}
	$menu[':class']['adminFileClipArea']= 'adminFileClipArea';
	$menu[':style']['width']	= $w . 'px';
	$menu[':style']['height']	= $h . 'px';

	if ($href){
		$p					= makeProperty($property);
		$menu[':before']	= "<a href=\"$href\" $p>";
		$menu[':after']		= "</a>";
	}
	
	$style			= array();
	$style['width']	= $w . 'px';
	$style['height']= $h . 'px';
	$style['overflow']	= 'hidden';
	$style	= makeStyle($style);
	
	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImageClip',
		'content'	=> &$storage);
	//	Получить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);
	if (!is_array($storage)) $storage = array();
	$offset	= (int)$storage[$uploadFolder]["$w/$h"] . 'px';

	beginAdmin($menu);
	$property['style']	= "top: $offset";
	echo "<div class=\"adminImageClip\" $style>";
	foreach($files as $path)
	{
		list($iw, $ih)	= getimagesize($path);
		$r	= $h?$w/$h:0;
		$ir	= $ih?$iw/$ih:0;
		if ($r > $ir){
			$property['width']	= "100%";
			$property['height']	= "auto";
		}else{
			$property['width']	= "auto";
			$property['height']	= "100%";
		}
		
		$property['src']= globalRootURL . imagePath2local($path);
		$p				= makeProperty($property);
		echo "<img $p />";
	}
	echo "</div>";
	endAdmin();

	m('script:jq');
	m('script:fileUpload');
	m('fileLoad', 'css/adminClip.css');
	m('fileLoad', 'script/jQuery.adminImageClipEx.js');

	return $menu;
}
//	+function file_imageClipUpload
function file_imageClipUpload(&$val, &$data)
{
	setTemplate('');
	
	$storeID	= getValue('storeID');
	$clip		= getValue('clip');
	$uploadFolder	= getValue('uploadFolder');
	if (!canEditFile($uploadFolder)) return;
	
	list($w, $h)	= explode('x', $clip);

	$storage	= array();
	$ev			= array(
		'id'	=> $storeID,
		'name'	=> 'fileImageClip',
		'content'	=> &$storage);
	//	Получить локальное хранилище для манипуляций изображением и настройки
	event('storage.get', $ev);
	if (!is_array($storage)) $storage = array();
	
	$storage[$uploadFolder]["$w/$h"]	= (int)getValue('top');
	event('storage.set', $ev);

	$data	= array('uploadFolder' => $uploadFolder);
	$files	= module("file:imageGet:$storeID", $data);
	foreach($files as $path){
		unlinkAutoFile($path);
	}
	clearCache();
}
?>