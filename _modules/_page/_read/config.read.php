<?
//	Ссылка на редактирование текстового блока
addUrl('read_edit_(.+)',			'read_edit');
//	Правило доступа для текстового блока
addAccess('text:(.*)',				'read_access');
addAccess('file:.+/(.+)/(Image)/.*','read_file_access');
?>