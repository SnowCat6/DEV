<?
addURL('backup_all', 			'backup:all');
addURL('backup_([\d\w-]+)', 	'backup:restore');
addURL('backup_now', 			'backup:backup');

addAccess('backup',				'backup:access');
addAccess('backup:([\d\w-]+)',	'backup:access');
?>