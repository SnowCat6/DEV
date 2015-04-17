<?
/*************************/
//	+function file_imageSizeMenu
function file_imageSizeMenu(&$storeID, &$data)
{
	$property	= $data['property'];
	$menu		= $data['adminMenu'];
	if (!is_array($menu)) $menu = array();

	$menu[':type']	= $data['hasAdmin'];
	
	$uploadFolder	= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);

	$menu['Загрузить']	= array(
		'class'	=> 'adminImageSizeUploadEx',
		'rel'	=> json_encode(array('uploadFolder' => $uploadFolder)),
		'href'	=> getURL('#'),
		'title'	=> 'Загрузить изображение'
	);
	
	$files	= module("file:imageGet:$storeID", $data);
	
	$size	= $data['size'];
	if (!is_array($size)) $size	= explode('x', $size);
	
	if (count($size) > 1){
		$w	= $size[0];
		$h	= $size[1];
	}else{
		$w	= $size[0];
		$h	= 0;
	}
	
	$style		= array();
	$style['max-width']	= $w . 'px';
	$style['width']		= $w . 'px';

	$menu[':style']['max-width']	= $w.'px';
	$menu[':style']['width']		= $w.'px';
	
	if ($h){
		$style['max-height']			= $h . 'px';
		$menu[':style']['max-height']	= $h . 'px';
	}

	if ($h && count($files) == 0){
		$style['height']			=  $h . 'px';
		$menu[':style']['height']	= $h . 'px';
	}
	
	$style	= makeStyle($style);
	
	if (count($files) == 0){
		$menu[':class']['noImage']	= 'noImage';
	}

	$menu[":before"]	= "<div class=\"adminImageSize\" style=\"$style\">";
	$menu[":after"]		= '</div>';

	m('script:jq');
	m('script:fileUpload');
	m('fileLoad', 'script/jQuery.adminImageSizeEx.js');

	beginAdmin($menu);
	$property['width']	= $data['size'];

	foreach($files as $path)
	{
		$property['src']	= $path;
		if ($data['zoom'])
		{
			$property['rel']	= "lightbox$data[zoom]";
			$property['href']	= $path;
			m('script:lightbox');
		}
		moduleEx('image:displayThumbImage', $property);
	}
	endAdmin();

	return $menu;
}
?>
