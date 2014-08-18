<?
function module_htaccess(){
	htaccessMake();
}
function htaccessMake()
{
	$globalRootURL	= globalRootURL;
	$ctx = $ctxNow	= file_get_contents('.htaccess');
	$sitesRules		= '';
	
	$ini	= getGlobalCacheValue('ini');
	$sites	= getSiteRules();
	foreach($sites as $rule => $host){
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
		$ctx		= preg_replace('/(\s*# <= DEVCMS.* #=> DEVCMS\s*)/s', $sitesRules, $ctx);
	}else{
		$ctx	.= $sitesRules;
	}
	
	if ($ctx == $ctxNow) return true;
	return file_put_contents_safe('.htaccess', $ctx);
}
function htaccessMakeHost($hostRule, $hostName, &$ctx, &$htaccess)
{
	if (strncmp('http://', strtolower($hostName), 7) == 0){
		$ctx	.=
			"# $hostName\r\n".
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteRule .*	$hostName	[R=301,L]";
	}else{
		//	Папка с разположением сайта
		$localSiteFolder= sitesBase."/$hostName";
		//	Базовый адрес сайта типа "/"
		$globalRootURL	= globalRootURL;
		//	Адрес кешированных файлов сайта
		$localCacheURL	= "$globalRootURL/".globalCacheFolder."/$hostName/".localSiteFiles;
		//	Адрес файлов сайта
		$localSiteURL	= "$globalRootURL/$localSiteFolder";
		
		$ctx	.= 
			"\r\n".
			"# $hostName\r\n".
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_URI} !^/_|\.php$\r\n".
			"RewriteCond %{DOCUMENT_ROOT}$localSiteFolder%{REQUEST_URI} -f\r\n".
			"RewriteRule (.*)	$localSiteURL/$1 [L]\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_URI} !^/_|\.php$\r\n".
			"RewriteRule (.*)	$localCacheURL/$1 [L]\r\n".
			"";
	}
}
?>