<?
//	Ссылка на редактирование текстового блока
addUrl('read_edit_(.+)',			'read_edit');
//	Правило доступа для текстового блока
addAccess('text:(.*)',				'read_access');
addAccess('file:.+/(.+)/(Image)/.*','read_file_access');
//	Проверка на разрешения доступа к сттанице
addEvent('site.start',		'page_access');
addEvent('site.noPageFound','page_404');
addEvent('site.renderEnd',	'page:script');
?>