<?
function widget_siteHeadGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> 'Заголовок',
		'title'		=> 'Верх страницы',
		'exec'		=> 'widget:siteHead:[id]',
		'config'	=> array
		(
			'data.width'	=> array(
				'name'		=> 'Ширина места лого',
				'default'	=> '300px'
			),
			'data.logoSize'	=> array(
				'name'		=> 'Размеры лого (WxH)',
				'default'	=> '250'
			),
			'data.class'	=> array(
				'name'		=> 'class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> 'Подвал',
		'title'		=> 'Низ страницы',
		'exec'		=> 'widget:siteBottom:[id]',
		'config'	=> array
		(
			'data.width'	=> array(
				'name'		=> 'Ширина места лого',
				'default'	=> '300px'
			),
			'data.logoSize'	=> array(
				'name'		=> 'Размеры лого (WxH)',
				'default'	=> '250'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);
}
?>