<?
$mime		= array();
$mime['css']= 'text/css';
$mime['js']	= 'text/javascript';
setCacheValue(':StaticCompressMime', $mime);

$mimeEx	= implode('|', array_keys($mime));
setCacheValue(':StaticCompressMimeEx', $mimeEx);

$gini	= getGlobalCacheValue('ini');
if ($gini[':']['staticCompress'] == 'yes')
{
	addEvent('site.render:before',	'compress');
	addEvent('htaccess.inject',		'staticFilesCompress');
}
addEvent('admin.settings.global',	'admin:staticFilesCompress');

function module_staticFilesCompress($val, &$inject)
{
	$mime	= getCacheValue(':StaticCompressMime');
	$mimeEx	= getCacheValue(':StaticCompressMimeEx');
	
	$htaccess	= &$inject['before'];
	$htaccess	.= "\r\n".
	"# Accept g-zip encoding\r\n".
	"AddEncoding x-gzip .gz\r\n".
	"RewriteEngine On\r\n".
	"RewriteCond %{HTTP:Accept-encoding} gzip\r\n".
	"RewriteRule ^(_cache/([^/]+)/siteFiles/.*)\.($mimeEx)$	\$1\.\$3\.gz [QSA]\r\n";
	
	foreach($mime as $mimeEx => $mimeType){
		$htaccess .= "RewriteRule \.$mimeEx\.gz$ - [T=$mimeType,E=no-gzip:1]\r\n";
	}
}
?>