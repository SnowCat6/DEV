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
			'Ширина места лого'	=> array(
				'name'		=> 'data.width',
				'default'	=> '300px'
			),
			'Размеры лого (WxH)'	=> array(
				'name'		=> 'data.logoSize',
				'default'	=> '250'
			),
			'class'	=> array(
				'name'		=> 'data.class',
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
			'Ширина места лого'	=> array(
				'name'		=> 'data.width',
				'default'	=> '300px'
			),
			'Размеры лого (WxH)'	=> array(
				'name'		=> 'data.logoSize',
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