<?
function widget_holder_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Контейнер',
		'title'		=> 'Размещение любых визуальных элементов',
		'exec'		=> 'holder:[id]'
	);
}
?>