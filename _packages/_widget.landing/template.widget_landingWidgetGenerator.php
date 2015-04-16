<? function widget_landingWidgetGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Лендинг',
		'name'		=> 'Фон с информацией',
		'title'		=> 'Фоновая картинка я заголовком и текстом',
		'exec'		=> 'widget:landing1:[id]',
		'update'	=> 'widget:landingUpdate:[id]',
		'delete'	=> 'widget:landingDelete:[id]',
		'config'	=> array
		(
			'Размер фона (ШxВ)'	=> array(
				'name'		=> 'data.size',
				'default'	=> ''
			),
			'Цвет фона'	=> array(
				'name'		=> 'data.style.background',
				'default'	=> ''
			)
		)
	);
}
function widget_landingUpdate($id, &$widget)
{
	$property	= array();
	$propertyImg= array();
	
	$size		= $widget['data']['size'];
	if ($size){
		list($w, $h) = explode('x', $size);
		if ($w) $property['width']	= $w . 'px';
		if ($h) $property['height']	= $h . 'px';
		if ($h && $w){
//			$propertyImg['overlay']	= 'hidden';
//			$propertyImg['width']	= $w . 'px';
//			$propertyImg['height']	= $h . 'px';
		}
	}
	if (is_array($widget['data']['style'])){
		foreach($widget['data']['style'] as $name=>$val){
			$property[$name] = $val;
		}
	}
	
	$uploadFolder	= images . "/$widget[id]/Title";
	makeDir($uploadFolder);
	$widget['data']['uploadFolder']	= $uploadFolder;
	$widget['data']['imageSize']	= (int)$size;

	
	$style	= makeStyle($property);
	$widget['data'][':style'] = " style=\"$style\"";

	$style	= makeStyle($propertyImg);
	$widget['data'][':imageStyle'] = " style=\"$style\"";
}
function widget_landingDelete($id)
{
	m("file:unlink", images."/$id");
}
?>