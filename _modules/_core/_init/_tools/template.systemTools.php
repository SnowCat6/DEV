<?
/////////////////////////////////////////////
//	Выполнить скрипт на сайте
//	+function execPHPscript 
function execPHPscript($name)
{
	flushCache();
	$root	= globalRootPath;
	//	If HTTP not avalible, exec shell

	$cmd	= execPHPshell("$root/$name");
	//	Stop session for server unfreze
	session_write_close();
	//	Run command
	$log	= array();
	exec($cmd, $log);
	//	Start session
	if ($log){
		session_start();
		//	Reload cache
		createCache(true);
		return implode("\r\n", $log);
	}

	//	Prepare exec command
	$md5		= md5($name.time());
	$fileName	= "exec_$md5.txt";
	//	Stop session for server unfreze
	file_put_contents($fileName, $name);
	$url	= "http://$_SERVER[HTTP_HOST]/exec_shell.htm?exec_$md5";
	$log	= file_get_contents($url);
	if (is_bool($log) && function_exists('curl_init'))
	{
		$curl		= curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_POST, false);
		$log		= curl_exec($curl);
		$status		= curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($status != 202) $log = "Ошибка выполения запроса $status: $url";
		curl_close($curl);
	}
	unlink($fileName);
	//	Start session
	session_start();
	//	Reload cache
	createCache(true);
	return $log;
}
function execPHPshell($path)
{
	switch(nameOS())
	{
	case 'Windows':	return "php.exe $path";
	case 'Linux':	return "php $path";
	case 'OSX':
	case 'FreeBSD':	$php = PHP_BINDIR;
		return "$php/php $path";
	}
}
function nameOS(){
	$uname = strtolower(php_uname());
	if (strpos($uname, "darwin")!== false)	return 'OSX';
	if (strpos($uname, "win")	!== false)	return 'Windows';
	if (strpos($uname, "linux")	!== false)	return 'Linux';
	if (strpos($uname, "freebsd")!==false)	return 'FreeBSD';
}

/////////////////////////////////////////////
//	Создать .htaccess файл из настроек системы
//	+function htaccessMake
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
	"ErrorDocument 404 $globalRootURL/index.php\r\n".
	"\r\n".
	"RewriteEngine On\r\n".
	"RewriteBase  /\r\n".
	"# Disable rewrite loop\r\n".
	"RewriteRule ^(index|install)\.php	- [L]\r\n".
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