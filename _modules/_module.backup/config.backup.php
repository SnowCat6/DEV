<?
addURL('backup_all', 		'backup:all');
addURL('backup_([\d-]+)', 	'backup:restore');
addURL('backup_now', 		'backup:backup');

addAccess('backup',			'backup:access');
addAccess('backup:(.*)',	'backup:access');
?>