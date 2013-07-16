<?
//	Ссылка на редактирование текстового блока
addUrl('read_edit_(\w+)', 	'read_edit');
//	Правило доступа для текстового блока
addAccess('text:(.*)',		'read_access');
//	Проверка на разрешения доступа к сттанице
addEvent('site.end',	'page_access');
?>