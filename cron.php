<?
$sitesPath	= "_sites";
$thisPath	= dirname(__FILE__);
chdir($thisPath);

$chronLock = "_cache/chron.txt";
if (is_file($chronLock) && time() - filemtime($chronLock) < (int)ini_get('max_execution_time')){
	$host = file_get_contents($chronLock);
	if (!$host) return;
	file_put_contents($chronLock, '');
	return executeChron($host);
}
@unlink($chronLock);

$ini		= readIniFileCron("_modules/config.ini");
@$cron		= $ini[':cron'];
@$cronURL	= $cron['cronURL'];

if ($cronURL = 'http://getbest.ru/DEV/cron.php')
{
	@$dir		= opendir($sitesPath);
	while(@$file = readdir($dir))
	{
		if ($file == '.' || $file == '..') continue;
		$filePath = "$sitesPath/$file";
		if (!is_dir($filePath)) continue;

		file_put_contents($chronLock, $file);
//		executeChron($file);
//		break;
		@readfile($cronURL);
	}
}
@unlink($chronLock);

// прочитать INI из файла
function readIniFileCron($file)
{
	$group	= '';
	$ini	= array();
	@$f		= file($file, false);
	if (!$f) return array();
	
	foreach($f as $row){
		if (preg_match('#^\[(.+)\]#',$row,$var)){
			$group = trim($var[1]);
			$ini[$group] = array();
		}else
		if ($group && preg_match('#([^=]+)=(.*)#',$row,$var)){
			$v1 = $var[1]; $v2 = trim($var[2]);
			$ini[$group][$v1] = $v2;
		}
	}
	return $ini;
}
function executeChron($host){
	define('_CRON_', true);
	define('siteURL', $host);

	global $_SERVER;
	$_SERVER['REQUEST_URI'] = '/cron_synch.htm';
	include('index.php');
}
?>