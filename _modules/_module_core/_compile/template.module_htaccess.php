<?
function module_htaccess(){
	htaccessMake();
}
function htaccessMake()
{
	$globalRootURL	= globalRootURL;
	$ctx			= file_get_contents('.htaccess');
	$ctx			= preg_replace("/# <= [^>]*# => [^\s]+\s*/s", '', $ctx);
	
	$ctx	= preg_replace("/[\r\n]+/", "\r\n", $ctx);
	$ctx	= preg_replace("/# <= index.*# => index/s", '', $ctx);
	$ctx	.="\r\n".
	"# <= index\r\n".
	"AddDefaultCharset UTF-8\r\n".
	"ErrorDocument 404 /pageNotFound404\r\n".
	"RewriteEngine On\r\n".
	"RewriteRule \.htm$|pageNotFound404	$globalRootURL/index.php [NC,L]\r\n".
	"# => index\r\n";
	
	$ini	= getGlobalCacheValue('ini');
	$sites	= $ini[':globalSiteRedirect'];
	if ($sites && is_array($sites))
	{
		foreach($sites as $rule => $host){
			htaccessMakeHost($rule, $host, $ctx);
		}
	}else{
		$sites		= getGlobalCacheValue('HostSites');
		if (count($sites) != 1){
			foreach($sites as $host){
				$host = substr($host, strlen('_sites/'));
				htaccessMakeHost(preg_quote($host), $host, $ctx);
			}
		}else{
			list($ix, $host) = each($sites);
			$host = substr($host, strlen('_sites/'));
			htaccessMakeHost(".*", $host, $ctx);
		}
	}
	if ($ctx == file_get_contents('.htaccess')) return true;
	return file_put_contents_safe('.htaccess', $ctx);
}
function htaccessMakeHost($hostRule, $hostName, &$ctx)
{
	$safeName	= md5($hostRule);
	$ctx		= preg_replace("/# <= $safeName.*# => $safeName/s", '', $ctx);
	
	if (strncmp('http://', strtolower($hostName), 7) == 0){
		$c	=
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteRule .*	$hostName	[R=301,L]"
			;
	}else{
		//	Initialize image path
		$ini 			= readIniFile("_sites/$hostName/_modules/config.ini");
		$localImagePath = $ini[':images'];
		if (!$localImagePath) $localImagePath = 'images';
		$localImagePath = trim($localImagePath, '/');
		
		$globalRootURL = globalRootURL;
		$globalRootPath= globalRootPath;
		
		$c	= 
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteRule ^($localImagePath/.+)	$globalRootURL/_sites/$hostName/$1 [L]\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_FILENAME} !/_|\.php$\r\n".
			"RewriteRule (.+)	_cache/$hostName/siteFiles/$1 [L]\r\n".
		
//			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
//			"RewriteCond %{REQUEST_FILENAME} _editor/.*(fck_editorarea.css|fckstyles.xml)\r\n".
//			"RewriteCond $globalRootPath/_cache/$hostName/siteFiles/%1 -f\r\n".
//			"RewriteRule .*	_cache/$hostName/siteFiles/%1 [L]".
		'';
	}
	
	$ctx	.= "\r\n".
		"# <= $safeName\r\n".
		"$c\r\n".
		"# => $safeName\r\n";
}
?>