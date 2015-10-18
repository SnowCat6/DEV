<?
//	+function module_staticFilesCompress
function module_staticFilesCompress($val, &$inject)
{
	$gini	= getGlobalCacheValue('ini');
	if ($gini[':']['staticCompress'] != 'yes') return;
	
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