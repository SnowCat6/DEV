<?
function widget_page_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Заголовок страницы',
		'exec'		=> 'page:title'
	);
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Контент',
		'exec'		=> 'display'
	);
}
?>