<?
function module_htaccess(){
	htaccessMake();
}
function htaccessMake()
{
	$globalRootURL	= globalRootURL;
	$ctx = $ctxNow	= file_get_contents('.htaccess');
	$sitesRules		= '';
	//	Remove old .htaccess code
	$ctx	= preg_replace("/# <= index.*# => index/s", '', $ctx);
	
	$ini	= getGlobalCacheValue('ini');
	$sites	= getSiteRules();
	foreach($sites as $rule => $host){
		$sitesRules	.= "# $host\r\n";
		htaccessMakeHost($rule, $host, $sitesRules, $ctx);
	}
	
	$sitesRules	= "\r\n".
	"# <= DEVCMS\r\n".
	"AddDefaultCharset UTF-8\r\n".
	"ErrorDocument 404 /pageNotFound404\r\n".
	"RewriteEngine On\r\n".
	"RewriteRule \.htm$|pageNotFound404	$globalRootURL/index.php [NC,L]\r\n".
	"$sitesRules\r\n".
	"# => DEVCMS\r\n";

	if (preg_match('/# <= DEVCMS.*# => DEVCMS/s', $ctx)){
		$sitesRules	= str_replace("$", "\\$", $sitesRules);
		$ctx		= preg_replace('/\s*# <= DEVCMS.*# => DEVCMS\s*/s', $sitesRules, $ctx);
	}else{
		$ctx	.= $sitesRules;
	}
	
	if ($ctx == $ctxNow) return true;
	return file_put_contents_safe('.htaccess', $ctx);
}
function htaccessMakeHost($hostRule, $hostName, &$ctx, &$htaccess)
{
	$safeName	= md5($hostRule);
	//	Remove old .htaccess code
	$htaccess	= preg_replace("/# <= $safeName.*# => $safeName/s", '', $htaccess);
	
	if (strncmp('http://', strtolower($hostName), 7) == 0){
		$ctx	.=
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteRule .*	$hostName	[R=301,L]";
	}else{
		//	Initialize image path
		$ini 			= readIniFile("_sites/$hostName/_modules/config.ini");
		$localImagePath = $ini[':images'];
		if (!$localImagePath) $localImagePath = 'images';
		$localImagePath = trim($localImagePath, '/');
		
		$globalRootURL = globalRootURL;
		$globalRootPath= globalRootPath;
		
		$ctx	.= 
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteRule ^($localImagePath/.+)	$globalRootURL/_sites/$hostName/$1 [L]\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_FILENAME} !/_|\.php$\r\n".
			"RewriteRule (.+)	_cache/$hostName/siteFiles/$1 [L]\r\n".
			'';
	}
}
?>