<?
//	Проверка на разрешения доступа к сттанице
addEvent('site.start',		'page_access');
addEvent('site.noPageFound','page_404');
addEvent('site.renderEnd',	'page:script');
?>