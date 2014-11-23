<? function file_imageMenu(&$val, &$data)
{
	$property	= $data['property'];
	$menu		= $data['adminMenu'];
	if (!is_array($menu)) $menu = array();
	
	$uploadFolder		= $data['uploadFolder'];
	if (is_array($uploadFolder)) list(, $uploadFolder) = each($uploadFolder);

	$menu['Загрузить']	= array(
		'class'	=> 'adminImageUploadEx',
		'rel'	=> json_encode(array('uploadFolder' => $uploadFolder)),
		'href'	=> getURL('#'),
		'title'	=> 'Загрузить изображение'
	);

	$files	= module("file:imageGet:$storeID", $data);
	list(, $path) = each($files);
	if ($path){
		list($w, $h) = getimagesize($path);
		if ($w)	$menu[':style']['width']	= $w . 'px';
		if ($h)	$menu[':style']['height']	= $h . 'px';
	}

	$menu[':style']['min-height']	= '50px';
	$menu[':style']['min-width']	= '50px';

	$menu[":before"]	= "<div class=\"adminImage\" style=\"$style\">";
	$menu[":after"]		= '</div>';
	
	m('script:jq');
	m('script:fileUpload');
	m('fileLoad', 'script/jQuery.adminImageEx.js');
	
	beginAdmin($menu);
	foreach($files as $path){
		$property['src']	= $path;
		moduleEx('image:display', $property);
	}
	endAdmin();

	return $menu;
}?>