<?
function widget_docWidgetGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Документы',
		'name'		=> 'Поисковая панель',
		'title'		=> 'Поиск товаров и документов по запросу',
		'exec'		=> 'widget:docSearchPanel:[id]',
		'config'	=> array
		(
			'Типы документов'	=> array(
				'name'		=> 'data.search.type',
				'default'	=> 'article,product'
			),
			'Названия характеристик'	=> array(
				'name'		=> 'data.search.options.names',
				'default'	=> ''
			),
			'Названия групп свойств'	=> array(
				'name'		=> 'data.search.options.groups',
				'default'	=> 'productSearch'
			),
			'Показывать панель выбора'	=> array(
				'name'		=> 'data.search.options.hasChoose',
				'default'	=> '1'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);
}
?>