<? function widget_siteMenuGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Навигация',
		'name'		=> 'Вертикальное меню',
		'title'		=> 'Одноуровневое меню',
		'exec'		=> 'widget:siteMenu:[id]',
		'config'	=> array
		(
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Навигация',
		'name'		=> 'Горизонтальное меню',
		'title'		=> 'Одноуровневое меню',
		'exec'		=> 'widget:siteMenuInline:[id]',
		'config'	=> array
		(
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);
}?>