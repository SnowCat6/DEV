<?
//	Ссылка на редактирование текстового блока
addUrl('read_edit_(.+)','read_edit');
//	Правило доступа для текстового блока
addAccess('text:(.*)',	'read_access');
addAccess('file:(.*)',	'read_file_access');

addEvent('holder.widgets',	'read_widgets');
?>