<?
function widget_read_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Редактируемая зона',
		'desc'		=> 'Текстовой блок для размещения HTML с визуальным редактором',
		'exec'		=> 'read:[id]',
		'delete'	=> 'read_delete:[id]',
	);

	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Таблица',
		'desc'		=> 'Табличные данные',
		'exec'		=> 'table:[id]',
		'delete'	=> 'read_delete:[id]',
	);
}
?>