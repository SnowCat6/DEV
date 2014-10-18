<?
$mime		= array();
$mime['css']= 'text/css';
$mime['js']	= 'text/javascript';
setCacheValue(':StaticCompressMime', $mime);

$mimeEx	= implode('|', array_keys($mime));
setCacheValue(':StaticCompressMimeEx', $mimeEx);

addEvent('site.renderBefore',	'compress');
addEvent('htaccess.before',		'staticFilesCompress');

function module_staticFilesCompress($val, &$htaccess)
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

	if ($hasEncode)	
	{
		$sites	= implode('|', $hasEncode);
		$mime	= getCacheValue(':StaticCompressMime');
		$mimeEx	= getCacheValue(':StaticCompressMimeEx');
		
		$rules	= "\r\n".
		"# <= STATICCOMPRESS\r\n".
		
		"AddEncoding x-gzip .gz\r\n".
		"RewriteEngine On\r\n".
		"RewriteCond %{HTTP:Accept-encoding} gzip\r\n".
		"RewriteRule ^(_cache/($sites)/siteFiles/.*)\.($mimeEx)$	\\$1\.\\$3\.\gz [QSA]\r\n";
		
		foreach($mime as $mimeEx => $mimeType){
			$rules .= "RewriteRule \.$mimeEx\.gz$ - [T=$mimeType,E=no-gzip:1]\r\n";
		}
		
		$rules .= "# => STATICCOMPRESS\r\n";
	}else{
		$rules	= '';
	}

	if (preg_match('/# <= STATICCOMPRESS.*# => STATICCOMPRESS/s', $htaccess)){
		$htaccess	= preg_replace('/\s*# <= STATICCOMPRESS.*# => STATICCOMPRESS\s*/s', $rules, $htaccess);
	}else{
		$htaccess	= $rules . $htaccess;
	}
}
?>