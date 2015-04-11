<?
function widget_page_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Заголовок страницы',
		'title'		=> 'Название текущей страниы',
		'exec'		=> 'page:title'
	);
	$widgets[]		=	array(
		'category'	=> 'Страница',
		'name'		=> 'Контент',
		'title'		=> 'Содержимое текущей страницы',
		'exec'		=> 'display'
	);
}
?>