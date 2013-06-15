<?
addURL('backup_all', 			'backup:all');
addURL('backup_now', 			'backup:backup');
addURL('backup_([\d\w-]+)', 	'backup:restore');

addAccess('adminPanel',			'access:backup');
addAccess('backup',				'backup:access');
addAccess('backup:([\d\w-]+)',	'backup:access');

//	Повелитель архива
addRole('Администратор архивов',		'backup');
?>