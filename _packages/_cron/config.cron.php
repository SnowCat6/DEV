<?
addUrl('cron_all', 		'cron:all');
addUrl('cron_synch', 	'cron:synch');

addEvent('admin.tools.service',	'cron:tools');
addAccess('cron:(.*)',			'cron_access');
?>