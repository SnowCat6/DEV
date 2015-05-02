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
			'data.id'	=> array(
				'name'		=> 'Идентификатор',
				'type'		=> 'url',
				'default'	=> '[id]'
			)
		)
	);
}
?>