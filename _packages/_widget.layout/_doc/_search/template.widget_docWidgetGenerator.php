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
			'data.search.type'	=> array(
				'name'		=> 'Типы документов',
				'default'	=> 'article,product'
			),
			'data.search.options.names'	=> array(
				'name'		=> 'Названия характеристик',
				'default'	=> ''
			),
			'data.search.options.groups'	=> array(
				'name'		=> 'Названия групп свойств',
				'default'	=> 'productSearch'
			),
			'data.search.options.hasChoose'	=> array(
				'name'		=> 'Показывать панель выбора',
				'default'	=> '1'
			),
			'data.class'	=> array(
				'name'		=> 'class',
				'default'	=> ''
			)
		)
	);
}
?>