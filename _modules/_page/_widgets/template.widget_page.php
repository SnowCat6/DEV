<?
function widget_page_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Заголовок страницы',
		'desc'		=> 'Название текущей страниы',
		'exec'		=> 'page:title'
	);
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Контент',
		'desc'		=> 'Содержимое текущей страницы',
		'exec'		=> 'display'
	);
}
?>