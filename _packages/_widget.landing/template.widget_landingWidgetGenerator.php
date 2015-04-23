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
				'default'	=> '1100'
			),
			'Цвет фона'	=> array(
				'name'		=> 'data.style.background',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Лендинг',
		'name'		=> 'Фотоплитка',
		'title'		=> 'Фотографии одинакового размера с сылками на документы',
		'exec'		=> 'widget:landing2:[id]',
		'update'	=> 'widget:landingUpdate:[id]',
		'delete'	=> 'widget:landingDelete:[id]',
		'config'	=> array
		(
			'Размер плитки (ШxВ)'	=> array(
				'name'		=> 'data.elmSize',
				'default'	=> '200x200'
			),
			'Цвет фона'	=> array(
				'name'		=> 'data.style.background',
				'default'	=> ''
			),
			'Фильтр документов'	=> array(
				'name'		=> 'data.selector',
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
		'config'	=> array
		(
			'Ширина окна (ШxВ)'	=> array(
				'name'		=> 'data.width',
				'default'	=> '1100'
			),
			'Высота строки'	=> array(
				'name'		=> 'data.height',
				'default'	=> '400'
			),
			'Отступы'	=> array(
				'name'		=> 'data.padding',
				'default'	=> '4'
			),
			'Цвет фона'	=> array(
				'name'		=> 'data.style.background',
				'default'	=> ''
			),
			'Фильтр документов'	=> array(
				'name'		=> 'data.selector',
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
		'config'	=> array
		(
			'Размер изображения (ШxВ)'	=> array(
				'name'		=> 'data.size',
				'default'	=> '1100x800'
			),
			'Цвет фона'	=> array(
				'name'		=> 'data.style.background',
				'default'	=> ''
			),
			'Фильтр документов'	=> array(
				'name'		=> 'data.selector',
				'type'		=>	'doc_filter',
				'default'	=> '@!place:[id]'
			)
		)
	);
}
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
	if (!is_array($data['style']))
		$data['style'] = array();

	foreach($data['style'] as $name => $val){
		$style[$name] = $val;
	}
	$widget['data'][':style'] = makeStyle($style);


	
	$uploadFolder	= images . "/$widget[id]/Title";
	makeDir($uploadFolder);
	$widget['data']['uploadFolder']	= $uploadFolder;



	setDataValues($widget['data'][':selector'], $widget['data']['selector']);
}
function widget_landingDelete($id, $data)
{
	m("file:unlink", images."/$id");
}
?>