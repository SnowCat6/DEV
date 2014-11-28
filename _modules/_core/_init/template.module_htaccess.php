<?
function module_htaccess(){
	htaccessMake();
}
function htaccessMake()
{
	$globalRootURL	= globalRootURL;
	$modulesFolder	= modulesBase;
	$templatesFolder= templatesBase;
	$cacheFolder	= globalCacheFolder;
	$sitesFolder	= sitesBase;
	$sitesCache		= localSiteFiles;
	
	$denyFolders	= array();
	$denyFolders[]	= $cacheFolder;
	$denyFolders[]	= $sitesFolder;
	$denyFolders[]	= $modulesFolder;
	$denyFolders[]	= $templatesFolder;
	$denyFolders	= implode('|', $denyFolders);
	
	$sitesRules		= '';
	$ctx = $ctxNow	= file_get_contents('.htaccess');
	event('htaccess.before', $ctx);

	$ini	= getGlobalCacheValue('ini');
	$sites	= getSiteRules();
	foreach($sites as $rule => $host){
		htaccessMakeHost($rule, $host, $sitesRules, $ctx);
	}

	$inject				= array();
	$inject['before']	= '';
	$inject['after']	= '';
	event('htaccess.inject', $inject);
	
	$sitesRules	= "\r\n".
	"# <= DEVCMS\r\n".
	"# Inject custom rules before\r\n".
	"$inject[before]\r\n".
	
	"AddDefaultCharset UTF-8\r\n".
	"ErrorDocument 404 $globalRootURL/index.php?URL=pageNotFound404\r\n".
	"\r\n".
	"RewriteEngine On\r\n".
	"RewriteBase  /\r\n".
	"# Disable rewrite loop\r\n".
	"RewriteRule ^index	- [L]\r\n".
	"# Allow redirected files access\r\n".
	"RewriteRule ^($cacheFolder/[^/]+/$sitesCache/|$sitesFolder/[^/]+/)	- [L]\r\n".
	"# HTML pages handle and prevent access system filders\r\n".
	"RewriteRule ^(.*\.htm$|$denyFolders)	$globalRootURL/index.php [L]\r\n".
	"# Disable system folders access\r\n".
	"$sitesRules\r\n".
	"# Allow folders access\r\n".
	"RewriteRule /	- [L]\r\n".

	"# Inject custom rules after\r\n".
	"$inject[after]\r\n".

	"# Disable all uncached links\r\n".
	"RewriteRule .*(php|php3)$	$globalRootURL/index.php [L]\r\n".
	"# => DEVCMS\r\n";
	
	if (preg_match('/# <= DEVCMS.*# => DEVCMS/s', $ctx)){
		$sitesRules	= str_replace("$", "\\$", $sitesRules);
		$ctx		= preg_replace('/\s*# <= DEVCMS.*# => DEVCMS\s*/s', $sitesRules, $ctx);
	}else{
		$ctx	.= $sitesRules;
	}
	event('htaccess.after', $ctx);
	
	if ($ctx == $ctxNow) return true;
	return file_put_contents_safe('.htaccess', $ctx);
}
function htaccessMakeHost($hostRule, $hostName, &$ctx, &$htaccess)
{
	if (strncmp('http://', strtolower($hostName), 7) == 0){
		$ctx	.=
			"# $hostName\r\n".
			"RewriteCond %{HTTP_HOST} $hostRule [NC]\r\n".
			"RewriteRule .*	$hostName	[R=301,L]";
	}else{
		//	Физический к корню сайта
		$globalRootPath	= globalRootPath;
		//	Базовый адрес сайта типа "/"
		$globalRootURL	= globalRootURL;
		
		//	Папка с разположением сайта
		$localSiteFolder= sitesBase."/$hostName";
		//	Адрес кешированных файлов сайта
		$localCacheFolder= globalCacheFolder."/$hostName/".localSiteFiles;
		
		$ctx	.= 
			"\r\n".
			"# $hostName\r\n".
			"RewriteCond %{HTTP_HOST} $hostRule [NC]\r\n".
			"RewriteCond $globalRootPath/$localSiteFolder/$0 -f\r\n".
			"RewriteRule .*	$globalRootURL/$localSiteFolder/$0 [L]\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule [NC]\r\n".
			"RewriteCond $globalRootPath/$localCacheFolder/$0 -f\r\n".
			"RewriteRule .*	$globalRootURL/$localCacheFolder/$0 [L]\r\n".
			"";
	}
}
?>