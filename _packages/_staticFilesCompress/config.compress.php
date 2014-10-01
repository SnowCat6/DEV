<?
//addEvent('site.renderBefore',	'compress');
//addEvent('htaccess.before',		'staticFilesCompress');

function module_staticFilesCompress($val, &$htaccess)
{
	$hasEncode	= array();
	$sites		= getSiteRules();
	foreach($sites as $rule => $host){
		$iniFile= sitesBase."/$host/".modulesBase.'/config.ini';
		$ini	= readIniFile($iniFile);
		$bThis	= $ini[':packages']['_staticFilesCompress'];
		if ($bThis) $hasEncode[]	= preg_quote($host);
	}

	if ($hasEncode)	
	{
		$sites	= implode('|', $hasEncode);
		
		$rules	= "\r\n".
		"# <= STATICCOMPRESS\r\n".
		
		"RewriteEngine On\r\n".
		"RewriteCond %{HTTP:Accept-encoding} gzip\r\n".
		"RewriteRule ^(_cache/($sites)/siteFiles/.*)\.(css|js)$	\\$1\.\\$3\.\gz [QSA]\r\n".
	
		"AddEncoding x-gzip .gz\r\n".
		"RewriteRule \.css\.gz$ - [T=text/css,E=no-gzip:1]\r\n".
		"RewriteRule \.js\.gz$ - [T=text/javascript,E=no-gzip:1]\r\n".
		
		"# => STATICCOMPRESS\r\n";
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