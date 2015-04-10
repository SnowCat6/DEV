<?
function module_read_widgets($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Редактируемая зона',
		'module'	=> 'read:[data.id]',
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