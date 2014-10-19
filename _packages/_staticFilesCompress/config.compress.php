<?
$mime		= array();
$mime['css']= 'text/css';
$mime['js']	= 'text/javascript';
setCacheValue(':StaticCompressMime', $mime);

$mimeEx	= implode('|', array_keys($mime));
setCacheValue(':StaticCompressMimeEx', $mimeEx);

addEvent('site.renderBefore',	'compress');
addEvent('htaccess.inject',		'staticFilesCompress');

function module_staticFilesCompress($val, &$inject)
{
	$hasEncode	= array();
	$sites		= getSiteRules();
	foreach($sites as $rule => $host)
	{
		$iniFile= sitesBase."/$host/".modulesBase.'/config.ini';
		$ini	= readIniFile($iniFile);
		$bThis	= $ini[':packages']['_staticFilesCompress'];
		if ($bThis) $hasEncode[]	= preg_quote($host);
	}
	if (!$hasEncode) return;
	
	$sites	= implode('|', $hasEncode);
	$mime	= getCacheValue(':StaticCompressMime');
	$mimeEx	= getCacheValue(':StaticCompressMimeEx');
	
	$htaccess	= &$inject['before'];
	$htaccess	.= "\r\n".
	"# Accept g-zip encoding\r\n".
	"AddEncoding x-gzip .gz\r\n".
	"RewriteEngine On\r\n".
	"RewriteCond %{HTTP:Accept-encoding} gzip\r\n".
	"RewriteRule ^(_cache/($sites)/siteFiles/.*)\.($mimeEx)$	\\$1\.\\$3\.gz [QSA]\r\n";
	
	foreach($mime as $mimeEx => $mimeType){
		$htaccess .= "RewriteRule \.$mimeEx\.gz$ - [T=$mimeType,E=no-gzip:1]\r\n";
	}
}
?>