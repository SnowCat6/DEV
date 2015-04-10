<?
function widget_read_config($val, &$widgets)
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
function widget_read($val, $data){
	//	this demo widget call "read" module
	//	never call use "widget:read" as module value
} ?>