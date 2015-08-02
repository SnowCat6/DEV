<?
addEvent('storage.get',	'file:storage:get');
addEvent('storage.set',	'file:storage:set');

addAccess('file:(.*)',	'file_file_access');
?>