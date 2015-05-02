<? function widget_landingWidgetGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Лендинг',
		'name'		=> 'Фотоплитка',
		'title'		=> 'Фотографии одинакового размера с сылками на документы',
		'exec'		=> 'widget:landing2:[id]',
		'update'	=> 'widget:landingUpdate:[id]',
		'delete'	=> 'widget:landingDelete:[id]',
		'preview'	=> 'widget:landingPreview:[id]=image:design/preview_landing2.jpg',
		'config'	=> array
		(
			'data.style.width'	=> array(
				'name'		=> 'Ширина окна',
				'default'	=> '1100'
			),
			'data.elmSize'	=> array(
				'name'		=> 'Размер плитки (ШxВ)',
				'default'	=> '220x220'
			),
			'data.style.background'	=> array(
				'name'		=> 'Цвет фона',
				'default'	=> ''
			),
			'data.selector'	=> array(
				'name'		=> 'Фильтр документов',
				'type'		=>	'doc_filter',
				'default'	=> '@!place:[id]'
			)
		)
	);
	$widgets[]		=	array(
		'category'	=> 'Лендинг',
		'name'		=> 'Фотоплитка 2',
		'title'		=> 'Фотографии разного размера с сылками на документы',
		'exec'		=> 'widget:landing3:[id]',
		'update'	=> 'widget:landingUpdate:[id]',
		'delete'	=> 'widget:landingDelete:[id]',
		'preview'	=> 'widget:landingPreview:[id]=image:design/preview_landing3.jpg',
		'config'	=> array
		(
			'data.width'	=> array(
				'name'		=> 'Ширина окна',
				'default'	=> '1100'
			),
			'data.height'	=> array(
				'name'		=> 'Высота строки',
				'default'	=> '400'
			),
			'data.padding'	=> array(
				'name'		=> 'Отступы',
				'default'	=> '4'
			),
			'data.style.background'	=> array(
				'name'		=> 'Цвет фона',
				'default'	=> ''
			),
			'data.selector'	=> array(
				'name'		=> 'Фильтр документов',
				'type'		=>	'doc_filter',
				'default'	=> '@!place:[id]'
			)
		)
	);
	$widgets[]		=	array(
		'category'	=> 'Лендинг',
		'name'		=> 'Фото документов',
		'title'		=> 'Титульная фотография документов',
		'exec'		=> 'widget:landing4:[id]',
		'update'	=> 'widget:landingUpdate:[id]',
		'delete'	=> 'widget:landingDelete:[id]',
		'preview'	=> 'widget:landingPreview:[id]=image:design/preview_landing4.jpg',
		'config'	=> array
		(
			'data.size'	=> array(
				'name'		=> 'Размер изображения (ШxВ)',
				'default'	=> '1100x750'
			),
			'data.style.background'	=> array(
				'name'		=> 'Цвет фона',
				'default'	=> ''
			),
			'data.selector'	=> array(
				'name'		=> 'Фильтр документов',
				'type'		=> 'doc_filter',
				'default'	=> '@!place:[id]'
			)
		)
	);
}
//	+function widget_landingUpdate
function widget_landingUpdate($id, &$widget)
{
	$data			= $widget['data'];
	
	$style			= array();
	list($w, $h)	= explode('x', $data['elmSize']);
	if ($w) $style['width']	= $w . 'px';
	if ($h) $style['height']= $h . 'px';
	$widget['data'][':elmStyle']	= makeStyle($style);

	
	$style		= array();
	$size		= $data['size'];
	if ($size)
	{
		list($w, $h) = explode('x', $size);
		if ($w) $style['width']	= $w . 'px';
		if ($h) $style['height']= $h . 'px';
	}

	if (!is_array($data['style']))	$data['style'] = array();
	foreach($data['style'] as $name => $val)
	{
		if ($name == 'width') $val = (int)$val . 'px';
		$style[$name] = $val;
	}
	$widget['data'][':style'] = makeStyle($style);


	
	$folder			= "widgets/$widget[id]";
	$imageFolder	= images . "/$folder";
	$widget['data']['folder']		= $folder;
	$widget['data']['imageFolder']	= $imageFolder;
	makeDir($imageFolder);




	setDataValues($widget['data'][':selector'], $widget['data']['selector']);
}
//	+function widget_landingDelete
function widget_landingDelete($id, $data)
{
	m("file:unlink", $data['imageFolder']);
}
//	+function widget_landingPreview
function widget_landingPreview($id, $data)
{
/*
	$widget	= module("holderAdmin:getWidget:$id");
	$exec	= $widget[':exec'];
	if ($exec['code']) return module($exec['code'], $exec['data']);
*/	
	$image	= getSiteFile($data['image']);
	if ($image){
		$p	= array('src' => $image);
		return module("image:display", $p);
	}
}
?>