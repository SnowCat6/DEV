<?
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//	apd_set_pprof_trace();
//	Засечем время начала работы
define('sessionTimeStart',	getmicrotime());
//	Уникальный номер сессии
define('sessionID', 		userIP().':'.sessionTimeStart);

//	Константы путей для размещения системных файлов
define('modulesBase',	'_modules');
define('templatesBase',	'_templates');
define('sitesBase',		'_sites');
define('configName',	'_sites/config.ini');
define('globalCacheFolder',	'_cache');
define('localCompilePath',	'compiledPages');
define('localSiteFiles',	'siteFiles');
define('localCompiledCode', 'modules.php');

//	Если запущен на очень старой версии PHP то определим недостающую функцию
if (!function_exists('file_put_contents')){
	function file_put_contents($name, &$data){
		$f = fopen($name, 'w'); $bOK = fwrite($f, $data); fclose($f);
		return $bOK;
	}
}
//	Переменная для хранения настроек текущей сессии
global $_CONFIG;
$_CONFIG = array();

//	Если запуск скрипта из консоли (CRON, командная строка) выполнить специфический код
if (defined('STDIN'))
	return consoleRun($argv);
	
//	Ограничить время работы скрипта, на некоторых хостингах иначе все работает некорректно
if ((int)ini_get('max_execution_time') > 60) set_time_limit(60);
//////////////////////
//	Инициализация данных, глобальный и локальный кеш, задание констант
ob_start();
globalInitialize();
localInitialize();
ob_end_clean();
//////////////////////
//	MAIN CODE
//////////////////////
header('Content-Type: text/html; charset=utf-8');
//////////////////////
$renderedPage	= NULL;
//	Отрисовать сайт
event('site.render',$renderedPage);
//	Обработчики GZIP и прочее
event('site.close',	$renderedPage);
//	Вывести в поток
echo $renderedPage;
//	Вывести все буффера
flush();
//////////////////////
//	FINAL AND CLEANUP
//	Возможно что-то ускорит при большой загрузке, полезно с fastcgi_finish_request
session_write_close();

//	Добавть время для фоновых процессов
set_time_limit((getmicrotime() - sessionTimeStart) + 5*60*60);

//	Вывести все данные и закрыть соединнение, если такая возможность есть
if (function_exists('fastcgi_finish_request ')){
	fastcgi_finish_request();
}
//	Постобработка, фоновые процессы, без вывода на экран
event('site.exit',	$_CONFIG);
flushCache();

///	Выполнить функцию по заданному названию, при необходимости подгрузить из файла
function module($fn, $data = NULL){
	list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn("module_$fn");
	return $fn?$fn($value, $data):NULL;
}
function moduleEx($fn, &$data){
	list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn("module_$fn");
	return $fn?$fn($value, $data):NULL;
}
//	Тоже самое что и module но возвращает выводимое значение
function m($fn, $data = NULL){
	return mEx($fn, $data);
}
function mEx($fn, &$data){
	ob_start();
	moduleEx($fn, $data);
	return ob_get_clean();
}

//	вызвать событие для всех обработчиков
function event($eventName, &$eventData)
{
	global $_CACHE;
	$event	= &$_CACHE['localEvent'];//getCacheValue('templates');
	$ev		= &$event[$eventName];
	if (!$ev) return;
	
	foreach($ev as &$module){
		moduleEx($module, $eventData);
	}
}

//	Добавть обработчик события
function addEvent($eventName, $eventModule){
	$event = getCacheValue('localEvent');
	$event[$eventName][$eventModule]	= $eventModule;
	setCacheValue('localEvent', $event);
}

//	Добавить обработчки URL страницы
function addUrl($parseRule, $parseModule){
	$localURLparse = getCacheValue('localURLparse');
	$localURLparse[$parseRule]	= $parseModule;
	setCacheValue('localURLparse', $localURLparse);
}

//	access
function addAccess($parseRule, $parseModule){
	$localAccessParse = getCacheValue('localAccessParse');
	$localAccessParse[$parseRule][$parseModule]	= $parseModule;
	setCacheValue('localAccessParse', $localAccessParse);
}

//	roles
function addRole($roleName, $roleAccess){
	$localUserRoles = getCacheValue('localUserRoles');
	$localUserRoles[$roleAccess]	= $roleName;
	setCacheValue('localUserRoles', $localUserRoles);
}

//	Standart snippets
function addSnippet($snippetName, $value){
	$localSnippets = getCacheValue('localSnippets');
	$localSnippets[$snippetName]	= $value;
	if (!$value) unset($localSnippets[$snippetName]);
	setCacheValue('localSnippets', $localSnippets);
}

//	Получить указатель на функцию, при необходимости подгрзить файл
function getFn($fnName)
{
	if (function_exists($fnName)) return $fnName;

	global $_CACHE;
	$templates	= &$_CACHE['templates'];
	$template	= &$templates[$fnName];
	if (!$template) return NULL;

	$timeStart	= getmicrotime();
	ob_start();
	include_once($template);
	ob_end_clean();

	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $template file");
	if (function_exists($fnName)) return $fnName;

	//	Записать название несуществующей функции для предотвращения  повторного поиска
	$template	= '';
	module('message:fn:error', "Function not found '$fnName'");
	return NULL;
}

//	Прлучить запрашиваемый URL
function getRequestURL()
{
	//	Если путь передан через переменную, использовать ее
	$url	= $_GET['URL'];
	if ($url) return "/$url";
	//	Получть из переменной сервера
	$url	= $_SERVER['REQUEST_URI'];
	$url	= substr($url, strlen(globalRootURL));
	//	Удалить все символы после спецсимволов, оставить только основной путь
	return preg_replace('@[#?].*@', '', $url);
}

//	Получить локальный путь к папке с файлами сайта
function getSitePath($siteURL)
{
	$sites		= getGlobalCacheValue('HostSites');
	if (isset($sites[$siteURL])) return $sites[$siteURL];
	return "_sites/$siteURL";
}

//	Получить адрес текущего сайта
function getSiteURL()
{
	if (defined('siteURL')) return siteURL;
	//	Получить адрес сайта
	$siteURL	= preg_replace('#^www\.#', '', $_SERVER['HTTP_HOST']);
	//	Найти по правилам сайт
	$sitesRules	= getSiteRules();
	foreach($sitesRules as $rule => $host){
		if (preg_match("#$rule#i", $siteURL)){
			define('siteURL', $host);
			return siteURL;
		}
	}
	define('siteURL', 'default');
	return siteURL;
}
function getSiteRules(){
	//	Полуить список правил для сайтов
	$ini		= getGlobalCacheValue('ini');
	$sitesRules	= $ini[':globalSiteRedirect'];
	//	Если правила заданы, вернуть настройки
	if ($sitesRules) return $sitesRules;
	
	$sitesRules	= getGlobalCacheValue('sitesRules');
	//	Сформировать автоматически
	$sitesRules = array();
	$sites		= getDirs(sitesBase);
	foreach($sites as $site=>$path){
		$sitesRules[$site]	= $site;
	}
	//	Добавить правила для неизвестных сайтов
	if (count($sitesRules) == 1){
		//	Если сайт только один, то все адреса будут показывать его
		list($site) 		= each($sitesRules);
		$sitesRules			 = array();
		$sitesRules[".*"]	= $site;
	}else{
		$sitesRules[".*"]	= 'default';
	}
	setGlobalcacheValue('sitesRules', $sitesRules);
	return $sitesRules;
}
// прочитать INI из файла
function readIniFile($file)
{
	m("message:trace", "Read ini $file");

	$group	= '';
	$ini	= array();
	$f		= file($file, false);
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

//	Записать INI а файл
function writeIniFile($file, &$ini)
{
	$out = '';
	reset($ini);
	foreach ($ini as $name => &$v){
		if (!is_array($v)) continue;
		$out .= "[$name]\r\n";
		foreach($v as $name => $val){
			if (is_array($val)) continue;
			$out .= "$name=$val\r\n";
		}
	}

	return file_put_contents_safe($file, $out);
}

/////////////////////////////////////////
//	Работа с кешем
/////////////////////////////////////////

//	Зваисать значения на диск
function writeData($path, &$data)
{
	memSet("data:$path", $data);
	if ($data) return file_put_contents_safe($path, serialize($data));
	unlink($path);
	return true;
}

function readData($path)
{
	$data	= memGet("data:$path");
	if ($data) return $data;

	m("message:trace", "Read data $path");
	$data	= unserialize(file_get_contents($path));
	memSet("data:$path", $data);
	return $data;
}
//	Глобальный кеш
function globalCacheExists(){
	$ini		= getGlobalCacheValue('ini');
	$bNoCache	= $ini[':']['useCache'];
	return $bNoCache == 1;
}

function setGlobalCacheValue($name, &$value){
	$GLOBALS['_GLOBAL_CACHE_NEED_SAVE']	= true;
	$GLOBALS['_GLOBAL_CACHE'][$name]	= $value;
}
function getGlobalCacheValue($name){
	return $GLOBALS['_GLOBAL_CACHE'][$name];
}
function testGlobalCacheValue($name){
	return isset($GLOBALS['_GLOBAL_CACHE'][$name]);
}

//	Локальный кеш
function localCacheExists()
{
	if (defined('localCacheExists')) return localCacheExists;
	
	$ini		= getCacheValue('ini');
	$bNoCache	= $ini[':']['useCache'];
	define('localCacheExists', $bNoCache == 1);
	return localCacheExists;
}

function setCacheValue($name, &$value){
	$GLOBALS['_CACHE_NEED_SAVE']= true;
	$GLOBALS['_CACHE'][$name]	= $value;
}
function getCacheValue($name){
	return $GLOBALS['_CACHE'][$name];
}
function testCacheValue($name){
	return isset($GLOBALS['_CACHE'][$name]);
}

//	Выгрузить кеш, если в нем были изменения
function flushCache($bIgonoreCacheTime = false)
{
	if (defined('clearCacheCode')){
		$site	= getSiteURL();
		return execPHP("index.php clearCacheCode $site");
	}
	if (defined('clearCache')){
		$site	= getSiteURL();
		return execPHP("index.php clearCache $site");
	}

	global $_CACHE_NEED_SAVE, $_CACHE;
	if ($_CACHE_NEED_SAVE && localCacheExists())
	{
		$cacheFile	= localCacheFolder.'/cache.txt';
		if ($bIgonoreCacheTime || filemtime($cacheFile) == cacheFileTime){
			if (!writeData($cacheFile, $_CACHE)){
				echo 'Error write cache';
			};
		}
		$_CACHE_NEED_SAVE = FALSE;
	}
	
	global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
	if ($_GLOBAL_CACHE_NEED_SAVE && globalCacheExists()){
		if (!writeData(globalCacheFolder.'/globalCache.txt', $_GLOBAL_CACHE)){
			echo 'Error write global cache';
		};
		$_GLOBAL_CACHE_NEED_SAVE = false;
	}
}
function clearCacheCode()
{
	define('clearCacheCode', true);
}
function clearCache()
{
	define('clearCache', true);
}

//	add		=> doc:page:article
//	add		=> doc:57:article
//	write	=> doc:57
//	restore	=> backup:restoreFolderName
function access($val, $data)
{
	$cacheAccess		= &$GLOBALS['_CONFIG']["access:$val:$data"];
	if (isset($cacheAccess)) return (bool)$cacheAccess;
	
	$cacheAccess= false;
	$cache		= &$GLOBALS['_CACHE'];
	$parseRules	= &$cache['localAccessParse'];
	foreach($parseRules as $parseRule => &$access)
	{
		if (!preg_match("#^$parseRule$#", $data, $v)) continue;
		foreach($access as &$parseModule){
			$r = moduleEx("$parseModule:$val", $v);
			if (!is_bool($r)) continue;
			if ($r) return $cacheAccess	= true;
		}
	}
}


function userIP(){
	return GetIntIP($_SERVER['REMOTE_ADDR']);
}
//	Получить адрес клиента
function GetIntIP($src){
  $t = explode('.', $src);
  return count($t) != 4 ? 0 : 256 * (256 * ((float)$t[0] * 256 + (float)$t[1]) + (float)$t[2]) + (float)$t[3];
}
//	Вернуть адрес клиента ввиде строки
function GetStringIP($src){
  $s1 = (int)($src / 256);
  $i1 = $src - 256 * $s1;
  $src = (int)($s1 / 256);
  $i2 = $s1 - 256 * $src;
  $s1 = (int)($src / 256);
  return sprintf('%d.%d.%d.%d', $s1, $src - 256 * $s1, $i2, $i1);
}

//	Удалить дерево директорий с файлами
function delTree($dir, $bRemoveBase = true, $bUseRename = false)
{
	$dir	= rtrim($dir, '/');
	if ($bUseRename){
		$rdir	= "$dir.del";
		@rename($dir, $rdir);
		if (!$bRemoveBase) makeDir($dir);
		$dir	= $rdir;
	}

	$d		= opendir($dir);
	if (!$d) return;
	
	while(($file = readdir($d)) != null){
		if ($file == '.' || $file == '..') continue;
		$file = "$dir/$file";
		if (is_file($file))	unlink($file);
		else
		if (is_dir($file)) delTree($file, true, false);
	}
	closedir($d);
	if ($bRemoveBase || $bUseRename) @rmdir($dir);
}

/****************************************/
//	CONSOLE
/****************************************/
function consoleRun(&$argv)
{
	switch($argv[1]){
	//	Recompile changed files and cleanup cache
	case 'clearCache':
		$site	= $argv[2];
		if (!$site) return;
		
		echo "Clearing cache $site";
		executeCron($site, '/');
		globalInitialize();
		compileFiles(localCacheFolder);
		flushCache(true);
		memClear();
		return;
	//	Remove all cached files, compile all code, clean cache
	case 'clearCacheCode':
		$site	= $argv[2];
		if (!$site) return;

		echo "Clearing cache code $site";
		executeCron($site, '/');
		globalInitialize();

		$tmpCache = localCacheFolder.'.compile';
		$tmpCache2= localCacheFolder.'.tmp';

		//	Удалить предыдущий кеш, если раньше не удалось
		delTree($tmpCache);
		if (!compileFiles($tmpCache))
			return delTree($tmpCache);
		//	Переименовать кеш, моментальное удаление
		rename(localCacheFolder, $tmpCache2);
		rename($tmpCache, localCacheFolder);
		memClear();
		//	Если переименование удалось, то удалить временный кеш
		delTree($tmpCache2);
		flushCache(true);
		memClear();
		return;
	//	Cron's tasks tick
	default:
		//	Показать страницу
		if (count($argv) == 2 || count($argv) == 3){
			$site	= $argv[1];
			$url	= $argv[2];
			if (!$url){
				$url = '/cron_synch.htm';
				echo "Run cron $site$url\r\n";
			}
			executeCron($site, $url);
			globalInitialize();
			localInitialize();
			
			$renderedPage = NULL;
			event('site.render', $renderedPage);
			echo $renderedPage;
			
			flushCache();
			return;
		}else
		if (count($argv) != 1) return;
		echo "Run sites cron\r\n";
		cronTick($argv);
		return;
	}
}

function cronTick(&$argv)
{
	chdir(dirname(__FILE__));
	$cronLock	= "_cache/cron.txt";
	$cronLog	= "_cache/cron.log";
	
	//	Делаем через блокировочный файл, чтобы никогда не запустить 2 копии шедулера.
	if (is_file($cronLock) && time() - filemtime($cronLock) < 30*60*60)
	file_put_contents($cronLock, '');

	$fLog	= fopen($cronLog, 'w');

	$sites	= getDirs(sitesBase);
	foreach($sites as $site => $sitePath)
	{
		echo "Cron $site\r\n";
		file_put_contents($cronLock, $site);
		$time	= date('d.m.Y H:i:s');
		fwrite($fLog, "\r\n===========  Cron begin $time $site ==========\r\n");
		fwrite($fLog, execPHP("index.php $site"));
		$time	= date('d.m.Y H:i:s');
		fwrite($fLog, "\r\n===========  Cron end $time $site ==========\r\n");
	}
	fclose($fLog);
	unlink($cronLock);
}

function executeCron($host, $url)
{
	define('_CRON_', true);
	define('siteURL',$host);
	$_SERVER['REQUEST_URI'] = $url;
}
function execPHP($name)
{
	$log	= array();
	$root	= str_replace('\\', '/', dirname(__FILE__));
	
	$cmd	= execPHPshell("$root/$name");
	if ($cmd){
		//	Stop session for server unfreze
		session_write_close();
		//	Run command
		exec($cmd, $log);
		//	Start session
		session_start();
	}
	return implode("\r\n", $log);
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

/*********************************/
//	MEMCACHE
/**********************************/

//	set value
function memSet($key, &$value)
{
	if (!defined('memcache') || !$key) return NULL;

	global $memcacheObject;
	$url	= getSiteURL();
	$key	= "$url:$key";
	
	if (is_null($value)) return $memcacheObject->delete($key);
	return $memcacheObject->set($key, $value);
}
//	get value
function memGet($key)
{
	if (!defined('memcache') || !$key) return NULL;

	global $memcacheObject;
	$url	= getSiteURL();
	$key	= "$url:$key";
	$v		= $memcacheObject->get($key);
	return is_bool($v)?NULL:$v;
}
//	clear all stored values
function memClear($filter = NULL, $bClearAllCache = false)
{
	if (!defined('memcache')) return;
	
	global $memcacheObject;
	$url	= getSiteURL();
	//	By filter
	$f		= "#^$url:$filter#";
	//	All entry
	$f2		= "#^$url:#";

	$allSlabs	= $memcacheObject->getExtendedStats('slabs');
	$items		= $memcacheObject->getExtendedStats('items');
	foreach($allSlabs as $server => &$slabs)
	{
		foreach($slabs AS $slabId => &$slabMeta)
		{
			if (!is_int($slabId)) continue;
			
			$cdump = $memcacheObject->getExtendedStats('cachedump', $slabId);
			foreach($cdump AS $keys => &$arrVal) 
			{
				if (!is_array($arrVal)) continue;
				
				foreach($arrVal AS $key => &$v){                   
					if ($bClearAllCache){
						if (!preg_match($f2,$key)) continue;
					}else{
						if (!preg_match($f, $key)) continue;
					}
					$memcacheObject->delete($key);
				}
			}
		}
	}
	return;
}
//	begin render cache
function memBegin($key)
{
	$data = memGet($key);
	if (!is_null($data)){
		echo $data;
		return false;
	}
	pushStackName('memcache', $key);
	ob_start();
	return true;
}
//	end render cache
function memEnd()
{
	$key	= popStackName('memcache');
	$data	= ob_get_flush();
	memSet($key, $data);
}
function memEndCancel(){
	$key	= popStackName('memcache');
	ob_end_flush();
}
/****************************/
function pushStackName($label, $name){
	global $_CONFIG;
	$stack	= &$_CONFIG['nameStack'][$label];
	if (!is_array($stack)) $stack = array();
	array_push($stack, $name);
}
function popStackName($label){
	global $_CONFIG;
	return array_pop($_CONFIG['nameStack'][$label]);
}
function cacheLevel(){
	global $_CONFIG;
	$count	= 0;
	foreach($_CONFIG['nameStack'] as &$cache) $count += count($cache);
	return $count;
}

///////////////////////////////////////////
//	Функции инициализации данных
///////////////////////////////////////////

//	Задать глобальные конфигурационные данные для сайта
function globalInitialize()
{
	global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
	//////////////////////
	//	Загрузить глобальный кеш
	$_GLOBAL_CACHE_NEED_SAVE	= false;
	$_GLOBAL_CACHE				= readData(globalCacheFolder.'/globalCache.txt');
	if (!$_GLOBAL_CACHE) $_GLOBAL_CACHE = array();
	
	$bCacheExists = true;
	$ini = getGlobalCacheValue('ini');
	if (!is_array($ini))
	{
		$ini = readIniFile(configName);
		setGlobalCacheValue('ini', $ini);
		$bCacheExists = false;
	}

	////////////////////////////////////////////
	//	MEMCACHE
	////////////////////////////////////////////
	$memcache	= $ini[':memcache'];
	$server		= $memcache['server'];
	if ($server && class_exists('Memcache', false)){
		global $memcacheObject;
		$memcacheObject = new Memcache();
		if ($memcacheObject->pconnect($server))
			define('memcache', true);
	}
	//	Найти физический путь корня сайта
	$globalRootURL	= $ini[':']['globalRootURL'];
	if (!$globalRootURL){
		$globalRootURL	= substr($_SERVER['PHP_SELF'], 0, -strlen(basename($_SERVER['PHP_SELF'])));
		$ini[':']['globalRootURL']	= $globalRootURL;
		setGlobalIniValues($ini);
	}
	//	like /dev
	$globalRootURL	= rtrim($globalRootURL, '/');
	define('globalRootURL',	$globalRootURL);
	//	like /www/dev
	define('globalRootPath',str_replace('\\' , '/', dirname(__FILE__)));
	if (!$bCacheExists){
		memClear('', true);
	}
	localConfigure();
}
//	Задать константы путей для текущего сайта
function localConfigure(){
	//////////////////////
	define('localHost',			getSiteURL());
	define('localHostPath',		getSitePath(localHost));
	define('localRootURL',		localHost);
	define('localRootPath',		localHostPath);

	define('localCacheFolder',	globalCacheFolder.'/'.localHost);
	define('localConfigName',	localRootPath.'/_modules/config.ini');
}
//	Задать локальные конфигурационные данные для сесстии
function localInitialize()
{
	//	Если текущий сайт определено как перенаправление, осуществить редирект
	if (strncmp('http://', localHost, 7) == 0){
		ob_clean();
		header("Location: " . localHost);
		die;
	}
	
	//	Загрузить локальный кеш
	global $_CACHE_NEED_SAVE, $_CACHE;
	$_CACHE_NEED_SAVE	= false;
	$cacheFile			= localCacheFolder.'/cache.txt';
	define('cacheFileTime', filemtime($cacheFile));

	$timeStart		= getmicrotime();
	$_CACHE			= readData($cacheFile);
	if (!$_CACHE) $_CACHE = array();
	$timeCache		= round(getmicrotime() - $timeStart, 4);
	//////////////////////
	//	Задать локальные конфигурационные данные для сесстии
	$compileFile	= localCacheFolder.'/'.localCompiledCode;
	$ini			= getCacheValue('ini');
	if (!is_array($ini) || !is_file($compileFile))
	{
		compileFiles(localCacheFolder);
		if (defined('memcache'))	m("message:trace", "Use memcache");
	}else{
		//	Задать путь хранения изображений
		define('images', getCacheValue('localImagePath'));

		//	При необходимости вывести сообщения от модулей в лог
		$timeStart		= getmicrotime();
		ob_start();
		include_once($compileFile);
		$modules	= ob_get_clean();
		$time 		= round(getmicrotime() - $timeStart, 4);
		
		m('message:trace:modules', 	$modules);
		if (defined('memcache'))	m("message:trace", "Use memcache");
		m('message:trace:',			"$timeCache cache read $cacheFile");
		m("message:trace", "$time Included $compileFile file");
	}

}
//	Найти конфигурационные файлы, модули, выполнить настройки
function compileFiles($localCacheFolder)
{
	$ini 		= readIniFile(localConfigName);
	setCacheValue('ini', $ini);

	//	Initialize image path
	$localImagePath = $ini[':images'];
	if (!$localImagePath) $localImagePath = localHostPath.'/images';
	setCacheValue('localImagePath', $localImagePath);
	//	Задать путь хранения изображений
	if (!defined('images')) define('images', $localImagePath);

	$a = array();
	//	Initialize event array
	setCacheValue('localEvent', $a);
	//	Access rule parse
	setCacheValue('localAccessParse', $a);
	//	Initialize url parse values
	$localURLparse = $ini[':URLparse'];
	if (!is_array($localURLparse)) $localURLparse = array();
	setCacheValue('localURLparse', $localURLparse);
	//	Найти и инициализировать модули
	$localModules	= array();
	//	Сканировать местоположения основных модулей
	modulesInitialize(modulesBase,	$localModules);
	//	Сканировать местоположения шаблонов
	modulesInitialize(templatesBase,$localModules);
	//	Сканировать местоположения подгружаемых модулей
	$packages	= $ini[":packages"];
	if (is_array($packages)) {
		foreach($packages as $path){
			modulesInitialize($path, $localModules);
		}
	}
	//	Сканировать местоположения модулей сайта
	modulesInitialize(localHostPath.'/'.modulesBase,	$localModules);
	//	Сохранить список моулей
	setCacheValue('modules', $localModules);
	//	Обработать модули
	event('config.start',	$localCacheFolder);
	//	Скомпилировать шаблоны, скопировать измененные файлы
	event('config.prepare', $localCacheFolder);
	//	Инициализировать с загруженными модулями
	event('config.end', $ini);
	
	return true;
}
//	Поиск всех загружаемых модуле  и конфигурационных програм
function modulesInitialize($modulesPath, &$localModules)
{
	//	Поиск конфигурационных файлов и выполенение
	$configFiles	= getFiles($modulesPath, '^config\..*php$');
	foreach($configFiles as &$configFile){
		include_once($configFile);
	}

	//	Поиск модулей
	$files	= getFiles($modulesPath, '^module_.*php$');
	foreach($files as $name => &$path){
		// remove ext
		$name = preg_replace('#\.[^.]*$#',		'', $name);
		$localModules[$name] = $path;
	}
	
	//	Сканировать поддиректории
	$dirs = getDirs($modulesPath, '^_');
	foreach($dirs as &$path){
		modulesInitialize($path, $localModules);
	};
}

////////////////////////////////////
//	tools
////////////////////////////////////
function redirect($url){
	flushCache();
	ob_clean();
	module('cookie');
	header("Location: http://$_SERVER[HTTP_HOST]$url");
	die;
}
//	Счетчик некешируемых элементов, для запрета кеширования
function setNoCache(){
	$GLOBALS['_CONFIG']['noCache']++;
}
//	Получить количество блокировок кеширования
function getNoCache(){
	return $GLOBALS['_CONFIG']['noCache'];
}
//	Установть текйщий шаблон страницы
function setTemplate($template){
	$GLOBALS['_CONFIG']['page']['template'] = "page.$template";
}

//	set multiply values int local site config file
function setIniValues($data)
{
	$ini = readIniFile(localConfigName);
	if (!writeIniFile(localConfigName, $data)) return false;
	setCacheValue('ini', $data);
	if (!localCacheExists()){
		$a = NULL;
		writeData(localCacheFolder.'/cache.txt', $a);
	}
	return true;
}

//	set multiply values int local site config file
function setGlobalIniValues($data)
{
	if (!writeIniFile(configName, $data)) return false;
	setGlobalCacheValue('ini', $data);
	if (!globalCacheExists()){
		$a = NULL;
		writeData(globalCacheFolder.'/globalCache.txt', $a);
	}

	return true;
}
//	Получить кодировку отправленных клиентом данных
function getValueEncode()
{
	if (defined('ValueEncode')) return ValueEncode;
	
	$headers	= getallheaders();
	foreach($headers as $name => &$val)
	{
		if (strtolower($name) != 'content-type') continue;
		if (!preg_match('#charset\s*=\s*(.+)#i', $val, $v)) break;
		define('ValueEncode', $v[1]);
		return ValueEncode;
	}
	define('ValueEncode', NULL);
	return ValueEncode;
}
//	Получить значение переменной по имени из запроса
function getValue($name)
{
	$val = $_POST[$name];
	if (!$val) $val = $_GET[$name];
	removeSlash($val);
	return $val;
}
//	Проверить налиличе переменной в запросе
function testValue($name){
	return isset($_POST[$name]) || isset($_GET[$name]);
}
//	Удалить квотирование
function removeSlash(&$var)
{
	if (!get_magic_quotes_gpc()) return;
	if (is_array($var)){
		foreach($var as $ndx => &$val) removeSlash($val);
		reset($var);
	}else $var = stripslashes($var);
}

//	Возвращает время до принудительного закрытия сессии сервером, в секундах
function sessionTimeout()
{
	$maxTime	= (int)ini_get('max_execution_time');	//	seconds
	if (defined('_CRON_')) $maxTime = 5*60;	//	console run
	else
	if (connection_aborted()) return  0;
	return max(0, $maxTime - (getmicrotime() - sessionTimeStart));
}

//	Получить текущее время до миллисекунд
function getmicrotime(){ 
	list($usec, $sec) = explode(' ', microtime()); 
	return ((float)$usec + (float)$sec); 
}

//	создать папку по данному пути
function makeDir($path){
	$dir	= '';
	$path	= explode('/',str_replace('\\', '/', $path));
	foreach($path as &$name){
		$dir .= "$name/";
		if (is_dir($dir)) continue;
		mkdir($dir);
		chmod($dir, 0775);
	}
}

//	Применить к файлу права доступа, на некоторых хостингах иначе все работает плохо
function fileMode($path)
{
	if (!is_file($path)) return;
	chmod($path, 0666);
}

// записать гарантированно в файл, в случае неудачи старый файл остается
function file_put_contents_safe($file, $value)
{
	if ($value){
		makeDir(dirname($file));
		return file_put_contents($file, $value, LOCK_EX) != false;
	}
	unlink($file);
	return true;
}

//	Получить список файлов по фильтру
function getFiles($dir, $filter = '')
{
	if (is_array($dir)){
		$res = array();
		foreach($dir as &$path){
			$res = array_merge($res, getFiles($path, $filter));
		}
		return $res;
	}
	$files	= array();
	$d		= opendir($dir);
	while(($file = readdir($d)) != false)
	{
		if ($file=='.' || $file=='..') continue;
		$f = "$dir/$file";
		if ($filter && !preg_match("#$filter#i", $file)) continue;
		if (!is_file($f)) continue;
		$files[$file] = $f;
	}
	closedir($d);
	ksort($files);
	return $files;
}

//	Получить список каталогов по фильтру
function getDirs($dir, $filter = ''){
	$files	= array();
	$d		= opendir($dir);
	while(($file = readdir($d)) != false)
	{
		if ($file=='.' || $file=='..') continue;
		$f = "$dir/$file";
		if (!is_dir($f)) continue;
		if ($filter && !preg_match("#$filter#i", $file)) continue;
		$files[$file] = $f;
	}
	closedir($d);
	ksort($files);
	return $files;
}

//	Копировать всю папку и файлами
function copyFolder($src, $dst, $excludeFilter = '', $bFastCopy = false)
{
	if ($src == $dst) return true;
	makeDir($dst);

	$bOK	= true;
	$d		= opendir($src);
	while($file = readdir($d))
	{
		if ($excludeFilter && preg_match("#$excludeFilter#", $file)) continue;
		if ($file=='.' || $file=='..') continue;
		
		$source = "$src/$file";
		$dest	=  "$dst/$file";
		if (is_dir($source))
		{
			if ($bFastCopy && is_dir($dest)) continue;
			$bOK &= copyFolder($source, $dest, $excludeFilter);
		}else{
			if (filemtime($source) == filemtime($dest))continue;
			if (!copy($source, $dest)) $bOK = false;
			touch($dest, filemtime($source));
		}
	}
	closedir($d);
	return $bOK;
}
//	Получить хеш данных
function hashData(&$value){
	if (!is_array($value)) return md5($value);

	$hash = '';
	foreach($value as $key => &$val){
		$hash = md5($hash.$key.hashData($val));
	}
	return $hash;
}
?>
