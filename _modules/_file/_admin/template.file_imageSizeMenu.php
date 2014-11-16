<?
/*************************/
//	+function file_imageSizeMenu
function file_imageSizeMenu(&$storeID, &$data)
{
	$property	= $data['property'];
	$menu		= $data['adminMenu'];
	if (!is_array($menu)) $menu = array();
	
	$uploadFolder		= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);

	$menu['Загрузить']	= array(
		'class'	=> 'adminImageSizeUploadEx',
		'rel'	=> json_encode(array('uploadFolder' => $uploadFolder)),
		'href'	=> getURL('#'),
		'title'	=> 'Загрузить изображение'
	);
	
	$files	= file_imageGet($storeID, $data);
	$size	= explode('x', $data['size']);
	
	if (count($size) > 1){
		$w	= $size[0];
		$h	= $size[1];
	}else{
		$w	= $size[0];
		$h	= 0;
	}
	
	$style		= array();
	$style[]	= 'max-width:' . $w . 'px';
	$style[]	= 'width:' . $w . 'px';
	$style[]	= 'max-height:' . $h . 'px';

	$menu[':style']['max-width']	= $w.'px';
	$menu[':style']['width']		= $w.'px';
	$menu[':style']['max-height']	= $h.'px';

	if ($h && count($files) == 0){
		$style[]= 'height:' . $h . 'px';
		$menu[':style']['height']		= $h.'px';
	}
	$style	= implode(';', $style);
	
	if (count($files) == 0){
		$menu[':class']['noImage']	= 'noImage';
	}
	
	$menu[":before"]	= "<div class=\"adminImageSize\" style=\"$style\">";
	$menu[":after"]		= '</div>';

	m('script:jq');
	m('script:fileUpload');
	m('fileLoad', 'script/jQuery.adminImageSizeEx.js');

	beginAdmin($menu);
	$bOne				= $data['multi'] != 'true';
	$property['width']	= $data['size'];
	
	foreach($files as $path){
		$property['src']	= $path;
		moduleEx('image:displayThumbImage', $property);
		if ($bOne) break;
	}
	endAdmin();

	return $menu;
}
?>
