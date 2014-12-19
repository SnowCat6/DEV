<?
//	Выполнить скрипт на сайте
//	+function execPHPscript 
function execPHPscript($name)
{
	flushCache();
	$root	= str_replace('\\', '/', dirname(__FILE__));
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
?>