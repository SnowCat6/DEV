<? function widget_siteReadGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Документы',
		'name'		=> 'Каталог документов с поиском',
		'title'		=> 'Каталог документов с пнелью поиска',
		'exec'		=> 'doc:read:siteCatalog=[data.:selector]',
		'update'	=> 'widget:siteReadUpdate:[id]',
		'config'	=> array
		(
			'Фильтр документов'	=> array(
				'name'		=> 'data.selector',
				'type'		=> 'doc_filter',
				'default'	=> '@!place:[id]'
			),
			'Цвет фотна'	=> array(
				'name'		=> 'data.style.background',
				'type'		=> 'color',
				'default'	=> ''
			)
		)
	);
}?>

<?
function widget_siteReadUpdate($id, &$widget)
{

	$selector	= array();
	setDataValues($selector, $widget['data']['selector']);
	$selector[':data'][':style']	= makeStyle($widget['data']['style']);
	$widget['data'][':selector']	= $selector;
}
?>
