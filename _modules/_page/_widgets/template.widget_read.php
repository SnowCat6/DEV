<?
function widget_read_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Редактируемая зона',
		'title'		=> 'Текстовой блок для размещения HTML с визуальным редактором',
		'exec'		=> 'read:[data.id]',
		'delete'	=> 'read_delete:[data.id]',
		'config'	=> array(
			'Идентификатор'	=> array(
				'type'		=> 'url',
				'name'		=> 'data.id',
				'default'	=> '[id]'
			)
		)
	);
}
?>