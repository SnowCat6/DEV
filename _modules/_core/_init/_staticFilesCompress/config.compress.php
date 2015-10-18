<?
$mime		= array();
$mime['css']= 'text/css';
$mime['js']	= 'text/javascript';
setCacheValue(':StaticCompressMime', $mime);

$mimeEx	= implode('|', array_keys($mime));
setCacheValue(':StaticCompressMimeEx', $mimeEx);

addEvent('htaccess.inject',		'staticFilesCompress');
addEvent('site.render:before',	'compress');
addEvent('admin.settings.global','admin:staticFilesCompress');
?>