<?
define('DEV_CMS_VERSION', '0.1.7');

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

/*************************************************************************************/
//	Class autoload function
spl_autoload_register(function($class)
{
	global $_CACHE;
	@$classPath	= $_CACHE[':classes'][$class];
	if (!$classPath) return;
	
	$timeStart	= getmicrotime();
	ob_start();
	include_once $classPath;
	ob_end_clean();

	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $classPath file");
});
/*************************************************************************************/
//	Если запуск скрипта из консоли (CRON, командная строка) выполнить специфический код
if (defined('STDIN')) return consoleRun($argv);
$exeCommand	= explode('?', $_SERVER['REQUEST_URI']);
//	Если запуск консли через HTTP, проверить на наличие команды.
if (strpos('/exec_shell.htm', $exeCommand[0]) >= 0){
	$exe	= basename($exeCommand[1]);
	$argv	= file_get_contents("$exe.txt");
	//	Если файл считался, запустить консоль
	if ($argv) return consoleRun(explode(' ', $argv));
}
/*************************************************************************************/
//	Ограничить время работы скрипта, на некоторых хостингах иначе все работает не корректно
if ((int)ini_get('max_execution_time') > 60) set_time_limit(60);
//////////////////////
//	Инициализация данных, глобальный и локальный кеш, задание констант
initialize::siteInitialize();
//////////////////////
//	MAIN CODE
//	Установть правильные заголовки
header("$_SERVER[SERVER_PROTOCOL] 200 OK");
header('Status: 200 OK');
header('Content-Type: text/html; charset=utf-8');
//////////////////////
//	Начало метаданных
meta::begin();
meta::set(':URL', getRequestURL());
//////////////////////
$null	= NULL;
event('site.enter', 	$null);
event('site.initialize',$null);
//	Отрисовать сайт
$renderedPage	= NULL;
event('site.render',$renderedPage);
//	Обработчики GZIP и прочее
event('site.close',	$renderedPage);
//	Вывести в поток
//////////////////////
echo $renderedPage;
//	Вывести все буффера
flush();
//////////////////////
//	FINAL AND CLEANUP

//	Добавть время для фоновых процессов
set_time_limit(5*60*60);
//	Возможно что-то ускорит при большой загрузке, полезно с fastcgi_finish_request
session_write_close();
//	Вывести все данные и закрыть соединнение, если такая возможность есть
if (function_exists('fastcgi_finish_request')){
	fastcgi_finish_request();
}
//	Постобработка, фоновые процессы, без вывода на экран
event('site.exit',	$null);
flushCache();
flushGlobalCache();
//	Окончание метаданных
meta::end();

/***********************************************************************************/
///	Выполнить функцию по заданному названию, при необходимости подгрузить из файла
function module($fn, $data = NULL){
	list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn('module_' . $fn);
	return $fn?$fn($value, $data):NULL;
}
function moduleEx($fn, &$data){
	list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn('module_' . $fn);
	return $fn?$fn($value, $data):NULL;
}
//	Тоже самое что и module но возвращает выводимое в поток значение
function m($fn, $data = NULL){
	ob_start();
	list($fn, $value) = explode(':', $fn, 2);
	if ($fn = getFn('module_' . $fn)) $fn($value, $data);
	return ob_get_clean();
}
function mEx($fn, &$data){
	ob_start();
	list($fn, $value) = explode(':', $fn, 2);
	if ($fn = getFn('module_' . $fn)) $fn($value, $data);
	return ob_get_clean();
}

//	вызвать событие для всех обработчиков
//	При указании постфикса, выполнить только его, пример: event('site.start:before', $anyData);
function event($eventName, &$eventData)
{
	global $_CACHE;
	//	Получить зарегистрированные функции
	list($eventName, $eventPart)	= explode(':', $eventName, 2);
	$event	= $_CACHE['localEvent'][$eventName];
	if (!$event) return;
	
	//	Пройтись по всем событиям и вызвать обработчики, если они имеются
	$query	= $eventPart?explode(':', "before:$eventPart:fire:after"):array('before', 'fire', 'after');
	foreach($query as $eventStateName)
	{
		//	Получить обработчики
		if ($evQuery = $event[$eventStateName]){
			//	Вызвать все зарегистрированные функции
			foreach($evQuery as $moduleFn)
			{
				list($fn, $value) = explode(':', $moduleFn, 2);
				if ($fn = getFn('module_' . $fn))
					$fn($value, $eventData);
			}
		}
	}
}


//	Получить указатель на функцию, при необходимости подгурзить файл
function getFn($fn)
{
	global $_CACHE;
	$templates	= $_CACHE['templates'];

	if (!is_array($fn)) $fn = array($fn);
	
	//	Найти функцию специализированную для устройства
	if ($prefix = devicePrefix())
	{
		foreach($fn as $fnName)
		{
			$fn2	= $prefix . $fnName;
			if (function_exists($fn2)) return $fn2;
			if ($template = $templates[$fn2]){
				$fnName = $fn2;
				break;
			}
			if (function_exists($fnName)) return $fnName;
			if ($template = $templates[$fnName]) break;
		}
	}else{
		foreach($fn as $fnName){
			if (function_exists($fnName)) return $fnName;
			if ($template = $templates[$fnName]) break;
		}
	}
	if (!$template) return NULL;

	$timeStart	= getmicrotime();
	ob_start();
	include_once($template);
	ob_end_clean();

	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $template file");
	if (function_exists($fnName)) return $fnName;

	//	Записать название несуществующей функции для предотвращения  повторного поиска
//	$_CACHE['templates'][$fnName]	= '';
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

//	Получить адрес текущего сайта
function siteFolder()
{
	if (defined('siteURL')) return siteURL;
	//	Получить адрес сайта
/*
	$url	= $_SERVER['REQUEST_URI'];
	if (strncmp(strtolower($url), strtolower(globalRootURL), strlen(globalRootURL)) == 0)
	{
		$url	= explode('/', substr($url, strlen(globalRootURL) + 1));
		$url	= $url[0];
		if (is_dir(sitesBase . '/' . $url)) $siteURL = $url;
	}
*/	
	if (!$siteURL){
		$siteURL	= preg_replace('#^www\.#', '', $_SERVER['HTTP_HOST']);
	}

	//	Найти по правилам сайт
	$sitesRules	= getSiteRules();
	foreach($sitesRules as $rule => $host)
	{
		if (!preg_match("#$rule#i", $siteURL)) continue;
		define('siteURL', $host);
		return siteURL;
	}
	define('siteURL', 'default');
	return siteURL;
}
function getSiteRules()
{
	//	Полуить список правил для сайтов
	$ini		= getGlobalCacheValue('ini');
	$sitesRules	= $ini[':globalSiteRedirect'];
	//	Если правила заданы, вернуть настройки
	if ($sitesRules) return $sitesRules;
	
	//	Сформировать автоматически из названий папок
	$sitesRules = array();
	$sites		= getDirs(sitesBase);
	foreach($sites as $site => $path){
		$rule	= preg_quote($site, '#');
		$sitesRules["\b$site\b"]	= $site;
	}
	//	Добавить правила для неизвестных сайтов
	if (count($sitesRules) == 1){
		//	Если сайт только один, то все адреса будут показывать его
		list(, $site) 		= each($sitesRules);
		$sitesRules			= array();
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
	foreach(file($file, false) as $row)
	{
		if (preg_match('#\[(.+)\]$#', trim($row), $var))
		{
			$group		= trim($var[1]);
			$ini[$group]= array();
		}else
		if ($group && preg_match('#([^=]+)=(.*)#', $row, $var))
		{
			$v1 = $var[1]; $v2 = trim($var[2]);
			$ini[$group][$v1] = $v2;
		}
	}
	return $ini;
}

//	Записать INI а файл
function writeIniFile($file, $ini)
{
	$out	= array();
	foreach ($ini as $name => $v)
	{
		if (!is_array($v)) continue;

		$out[]	= "[$name]";
		foreach($v as $name => $val)
		{
			if (is_array($val)) continue;
			$out[]	= "$name=$val";
		}
	}
	
	return file_put_contents_safe($file, implode("\r\n", $out));
}

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

//	add		=> doc:page:article
//	add		=> doc:57:article
//	write	=> doc:57
//	restore	=> backup:restoreFolderName
function access($val, $data)
{
	$acc		= config::get(':access', array());
	$cacheAccess= $acc["$val:$data"];
	if (isset($cacheAccess)) return (bool)$cacheAccess;
	
	$parseRules	= $GLOBALS['_CACHE']['localAccessParse'];
	foreach($parseRules as $parseRule => $access)
	{
		if (!preg_match("#^$parseRule$#", $data, $v)) continue;
		foreach($access as $parseModule)
		{
			$r = moduleEx("$parseModule:$val", $v);
			if (!is_bool($r)) continue;
			if ($r)
			{
				$acc["$val:$data"]	= true;
				config::set(':access', $acc);
				return true;
			}
		}
	}
	$acc["$val:$data"]	= false;
	config::set(':access', $acc);
	return false;
}
function accessUpdate(){
	config::set(':access', array());
}
//	UserIP address
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
/****************************************/
//	CONSOLE
/****************************************/
function execPHP($name){
	return systemExec::execPHPscript($name);
}
function consoleRun($argv)
{
	chdir(dirname(__FILE__));

	meta::begin();
	switch($argv[1]){
	//	Recompile changed files and cleanup cache
	case 'clearCache':
		$site	= $argv[2];
		if (!$site) return;
		
		echo "Clearing cache $site";
		
		executeCron($site, '/');
		initialize::globalInitialize();
		compileFiles(cacheRoot);
		flushCache(true);
		memClear();
		
		echo " OK";
		break;
	//	Remove all cached files, compile all code, clean cache
	case 'clearCacheCode':
		$site	= $argv[2];
		if (!$site) return;

		echo "Clearing cache code $site";
		executeCron($site, '/');
		initialize::globalInitialize();

		$tmpCache = cacheRoot.'.compile';
		$tmpCache2= cacheRoot.'.tmp';
		//	Удалить предыдущий кеш, если раньше не удалось
		delTree($tmpCache);
		if (!compileFiles($tmpCache))
			return delTree($tmpCache);

		//	Переименовать кеш, моментальное удаление
		event('config.rebase', $tmpCache);
		rename(cacheRoot, $tmpCache2);
		rename($tmpCache, cacheRoot);
		//	Если переименование удалось, то удалить временный кеш
		delTree($tmpCache2);
		flushCache(true);
		memClear();

		echo " OK";
		break;
	//	Cron's tasks tick
	default:
		//	Показать страницу
		if (count($argv) == 2 || count($argv) == 3)
		{
			$site	= $argv[1];
			$url	= $argv[2];
			if (!$url){
				$url = '/cron_synch.htm';
				echo "Run cron $site$url\r\n";
			}
			executeCron($site, $url);
			initialize::siteInitialize();
			
			$renderedPage = NULL;
			event('site.render', $renderedPage);
			echo $renderedPage;
			
			flushCache();
			flushGlobalCache();
			return;
		}else
		if (count($argv) != 1) return;
		echo "Run sites cron\r\n";
		cronTick($argv);
		break;
	}
	meta::end();
}
function cronTick(&$argv)
{
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

	$_GET['URL'] 			= $url;
	$_SERVER['REQUEST_URI'] = $url;
	meta::set(":URL",	$url);
	meta::set(":CRON",	$host);
}
/****************************/
///////////////////////////////////////////
//	Функции инициализации данных
///////////////////////////////////////////
function checkCompileFiles()
{
	$files	= readData(cacheRoot . '/files.txt');
	if (!is_array($files)) return;

	foreach($files as $path => $filemtime)
	{
		if (filemtime($path) != $filemtime)
			return false;
	}
	return true;
}
//	Найти конфигурационные файлы, модули, выполнить настройки
function compileFiles($cacheRoot)
{
	global $_CACHE_NEED_SAVE, $_CACHE;
	$_CACHE_NEED_SAVE = true;
	$_CACHE	= array();

	ob_start();
	$ini 		= readIniFile(localConfigName);
	if (!is_array($ini)) $ini	= array();
	setCacheValue('ini', $ini);

	//	Initialize image path
	$localImagePath = $ini[':images'];
	if (!$localImagePath) $localImagePath = localRootPath.'/images';
	setCacheValue('localImagePath', $localImagePath);
	
	//	Задать путь хранения изображений
	define('images', $localImagePath);

	$a = array();
	//	Initialize event array
	setCacheValue('localEvent', $a);
	//	Access rule parse
	setCacheValue('localAccessParse', $a);
	//	Initialize url parse values
	$localURLparse = $ini[':URLparse'];
	if (!is_array($localURLparse)) $localURLparse = array();
	setCacheValue('localURLparse', $localURLparse);
	
	//	Файлы для отслеживания изменений
	$GLOBALS['_COMPILED'] = array();
	//	Найти и инициализировать модули
	$folders		= array(modulesBase, templatesBase);
	$localModules	= array();
	//	Поиск модулей в PHAR файлах
	$files	= findPharFiles('./');
	foreach($files as $dir)
	{
		$dir	= getDirs($dir);
		foreach($folders as $folder){
			modulesInitialize($dir[$folder], $localModules);
		}
	}
	foreach($folders as $folder){
		modulesInitialize($folder,	$localModules);
	}
	//	Сканировать используемые библиотеки
	event('config.packages',		$localModules);
	//	Сканировать местоположения модулей сайта
	modulesInitialize(localRootPath.'/'.modulesBase, $localModules);

	//	Сохранить список моулей
	setCacheValue('modules',$localModules);
	//	Обработать модули
	event('config.start',	$cacheRoot);
	//	Скомпилировать шаблоны, скопировать измененные файлы
	event('config.prepare',	$cacheRoot);
	//	Инициализировать с загруженными модулями
	event('config.end',		$cacheRoot);
	ob_end_clean();
	
	return true;
}
//	Поиск всех загружаемых модуле  и конфигурационных програм
function modulesInitialize($modulesPath, &$localModules)
{
	if (!$modulesPath) return;
	//	Поиск модулей в PHAR файлах
	$files	= findPharFiles($modulesPath);
	foreach($files as $name => $path){
		modulesInitialize($path, $localModules);
	}
	
	$dirs	= array();
	foreach (scanFolder($modulesPath) as $path)
	{
		$name	= basename($path);
		//	Сканировать поддиректории
		if (is_dir($path)){
			if ($name[0] == '_') $dirs[]	= $path;
		}else
		//	Поиск конфигурационных файлов и выполенение
		if (preg_match('#^config\..*\.php$#', $name)){
			include_once($path);
			addCompiledFile($path);
		}else
		//	Поиск модулей
		if (preg_match('#^module_(.*)\.php$#', $name, $v)){
			$name	= $v[1];
			$localModules[$name] = $path;
		}
	};

	foreach($dirs as $path){
		modulesInitialize($path, $localModules);
	};
}
function findPackages()
{
	$packages	= array();
	$folders	= array();

	$files		= findPharFiles('./');
	foreach($files as $path)	$folders[]	= "$path/_packages";

	$files		= findPharFiles('_packages');
	foreach($files as $path)	$folders[]	= $path;

	$folders[]	= '_packages';
	
	foreach(getDirs($folders) as $name => $path) $packages[$name] = $path;

	return $packages;
}
function findPharFiles($path)
{
	if (!extension_loaded("phar")) return;

	$files	= getFiles($path, '\.(phar|tar|zip)$');
	foreach($files as &$path) $path = "phar://$path";
	return $files;
}
function addCompiledFile($path)
{
	global $_COMPILED;
	$_COMPILED[$path]	= filemtime($path);
}
////////////////////////////////////
//	tools
////////////////////////////////////
//	Счетчик некешируемых элементов, для запрета кеширования
function setNoCache(){
	$noCache	= (int)config::get('noCache');
	config::set('noCache', $noCache + 1);
}
//	Получить количество блокировок кеширования
function getNoCache(){
	return config::get('noCache');
}
//	Установть текйщий шаблон страницы
function setTemplate($template){
	config::set('pageTemplate', $template);
}
function getTemplate(){
	return config::get('pageTemplate');
}
//	set multiply values int local site config file
function setIniValues($data)
{
	if (!writeIniFile(localConfigName, $data)) return false;
	setCacheValue('ini', $data);
	if (!localCacheExists()){
		$a = NULL;
		writeData(cacheRoot.'/cache.txt', $a);
	}
	return true;
}
function getIniValue($name){
	$ini = getCacheValue('ini');
	return $ini[$name];
}
function setIniValue($name, $data)
{
	$ini = getCacheValue('ini');
	$ini[$name]	= $data;
	setIniValues($ini);
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
//	Получить значение переменной по имени из запроса
function getValue($name)
{
	$val = $_POST[$name];
	if (!$val) $val = $_GET[$name];
	
	if (!$val){
		$qs		= explode('?', $_SERVER['REQUEST_URI'], 2);
		parse_str($qs[1], $val);
		$val	= $val[$name];
	}
	removeSlash($val);
	return $val;
}
//	Проверить налиличе переменной в запросе
function testValue($name)
{
	if (isset($_POST[$name]) || isset($_GET[$name]))
		return true;

	$qs		= explode('?', $_SERVER['REQUEST_URI'], 2);
	parse_str($qs[1], $val);
	return isset($val[$name]);
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
// записать гарантированно в файл, в случае неудачи старый файл остается
function file_put_contents_safe($file, $value)
{
	if ($value){
		makeDir(dirname($file));
		if (file_put_contents($file, $value, LOCK_EX) != false)
			return true;
		module('message:error', "Ошибка записи файла $file");
		return false;
	}
	unlink($file);
	return true;
}
//	Удалить дерево директорий с файлами
function delTree($dir, $bRemoveBase = true, $bUseRename = false)
{
	$dir	= rtrim($dir, '/');
	if (!$dir) return;
	
	if ($bUseRename)
	{
		$rdir = "$dir.del";
		delTreeRecurse($rdir);
		rename($dir, $rdir);
		if (!$bRemoveBase) makeDir($dir);
		delTreeRecurse($rdir);
		return rmdir($rdir);
	}
	delTreeRecurse($dir);
	if ($bRemoveBase) rmdir($dir);
}
function delTreeRecurse($dir)
{
	foreach (scanFolder($dir) as $file)
	{
		if(is_dir($file)){
			delTreeRecurse($file);
			rmdir($file);
		}else unlink($file); 
	} 
} 
//	создать папку по данному пути
function makeDir($path)
{
	$dir	= '';
	$path	= explode('/',str_replace('\\', '/', $path));
	foreach($path as &$name)
	{
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
//	Получить список файлов по фильтру
function getFiles($dir, $filter = '')
{
	$files	= array();
	foreach(scanFolder($dir, $filter) as $file)
	{
		if (!is_file($file)) continue;
		$files[basename($file)]	= $file;
	}
	ksort($files);
	return $files;
}
//	Получить список каталогов по фильтру
function getDirs($dir, $filter = '')
{
	$files	= array();
	foreach(scanFolder($dir, $filter) as $file)
	{
		if (!is_dir($file)) continue;
		$files[basename($file)]	= $file;
	}
	ksort($files);
	return $files;
}
//	Копировать всю папку и файлами
function copyFolder($src, $dst, $excludeFilter = '', $bFastCopy = false)
{
	if ($src == $dst) return true;

	$bOK	= true;
	foreach(scanFolder($src) as $source)
	{
		$name	= basename($source);
		if ($excludeFilter && preg_match("#$excludeFilter#", $name)) continue;
		
		$dest	=  "$dst/$name";
		if (is_dir($source))
		{
			if ($bFastCopy && is_dir($dest)) continue;
			$bOK &= copyFolder($source, $dest, $excludeFilter);
		}else{
			if (filemtime($source) == filemtime($dest)) continue;
			if (!copy($source, $dest))
			{
				makeDir(dirname($dest));
				if (!copy($source, $dest)) return false;
			}
			touch($dest, filemtime($source));
		}
	}
	return $bOK;
}
//	return array of files and directories in folder
function scanFolder($dir, $filter = '')
{
	$files	= array();
	if (!is_array($dir)) $dir	= array($dir);
	
	foreach($dir as $dirName)
	{
		$dirName= rtrim($dirName, '/');
		$d		= opendir($dirName?$dirName:'./');
		while(($file = readdir($d)) != false)
		{
			if ($file=='.' || $file=='..') continue;
			if ($filter && !preg_match("#$filter#i", $file)) continue;
			$files[]	= $dirName?"$dirName/$file":$file;
		}
		closedir($d);
	}
	return $files;
}
//	Получить хеш данных
function hashData($value)
{
	return md5(serialize($value));
}

/*****************************************/
//	MEMCACHE
/*****************************************/
function createMemCache($gIni)
{
	$memcache	= $gIni[':memcache'];
	$server		= $memcache['server'];
	if ($server && class_exists('Memcache', false))
	{
		global $memcacheObject;
		$memcacheObject = new Memcache();
		if ($memcacheObject->pconnect($server))
		{
			$url	= siteFolder();
			$key	= "$url:cacheIndex";
			//	Получить актуальный интекс кеша, при очистке кеша уведичивать на единицу
			$v		= (int)$memcacheObject->get($key);
			if ($v <= 0){
				$v	= 1;
				$memcacheObject->set($key, $v);
			}
			//	Префикс кеша для всех значений этой сессии
			define('memcache', "$url:$v:");
/*******************/
			function memSet($key, $value){
	if (!$key) return false;
	
	global $memcacheObject;
	$key	= memcache . $key;
	if (is_null($value)) return $memcacheObject->delete($key);
	return $memcacheObject->set($key, $value);
			}
/*******************/
			function memGet($key)
			{
	if (!$key) return NULL;
	
	global $memcacheObject;
	$v		= $memcacheObject->get(memcache.$key);
	return is_bool($v)?NULL:$v;
			}
/*******************/
			function memClear($key = '', $bClearAllCache = false)
			{
	global $memcacheObject;
	//	Удалить кеши всех сеччий
	$url	= siteFolder();
	//	Увеличить индекс кеша, сделать кеш недействительным для всех сессий
	$memcacheObject->increment("$url:cacheIndex");
			}	//	End memClear
/*************************************/
			return;
		}else{//	End memcache connect
//			echo "Ошибка соединения с Memcache server: $server";
		}
	}
	//	No memcache defininion, fake functions
	function memSet($key, &$value)	{ return false; }
	function memGet($key)			{ return NULL; }
	function memClear($key = '', $bClearAllCache = false){ return false; }
}
/*******************************************/
/////////////////////////////////////////
//	Работа с кешем
/////////////////////////////////////////
//	Загрузить локальный кеш
function createCache($bCreateIfExists = false)
{
	$cacheFile	= cacheRoot.'/cache.txt';
	if ($bCreateIfExists && !file_exists($cacheFile)) return;
	define('cacheFileTime', filemtime($cacheFile));
	
	global $_CACHE_NEED_SAVE, $_CACHE;
	$_CACHE_NEED_SAVE	= false;
	$_CACHE	= readData($cacheFile);
	if (!$_CACHE) $_CACHE = array();
}

//	Глобальный кеш
function globalCacheExists(){
	$ini		= getGlobalCacheValue('ini');
	$bNoCache	= $ini[':']['useCache'];
	return $bNoCache == 1;
}

function setGlobalCacheValue($name, $value){
	$GLOBALS['_GLOBAL_CACHE_NEED_SAVE']	= true;
	$GLOBALS['_GLOBAL_CACHE'][$name]	= $value;
}
function getGlobalCacheValue($name){
	return $GLOBALS['_GLOBAL_CACHE'][$name];
}
function testGlobalCacheValue($name){
	return isset($GLOBALS['_GLOBAL_CACHE'][$name]);
}
//	Выгрузить кеш, если в нем были изменения
function flushCache($bIgonoreCacheTime = false)
{
	global $_CACHE_NEED_SAVE, $_CACHE;
	if (!$_CACHE_NEED_SAVE || !localCacheExists()) return;

	$cacheFile	= cacheRoot.'/cache.txt';
	if ($bIgonoreCacheTime || filemtime($cacheFile) <= cacheFileTime)
	{
		if (!writeData($cacheFile, $_CACHE)){
			echo 'Error write cache';
		};
	}
	$_CACHE_NEED_SAVE = FALSE;
}
function flushGlobalCache()
{
	global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
	if ($_GLOBAL_CACHE_NEED_SAVE && globalCacheExists())
	{
		makeDir(globalCacheFolder);
		if (!file_put_contents(globalCacheFolder.'/globalCache.txt', serialize($_GLOBAL_CACHE))){
			echo 'Error write global cache';
		};
		$_GLOBAL_CACHE_NEED_SAVE = false;
	}
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
function setCacheValue($name, $value){
	$GLOBALS['_CACHE_NEED_SAVE']= true;
	$GLOBALS['_CACHE'][$name]	= $value;
}
function getCacheValue($name){
	return $GLOBALS['_CACHE'][$name];
}
function testCacheValue($name){
	return isset($GLOBALS['_CACHE'][$name]);
}
/*******************************/
function deviceDetect()
{
	$ini	= getCacheValue('ini');
	if (!$ini) return;
	if ($ini[':']['mobileView'] != 'yes'){
		define('isTablet',	false);
		define('isPhone',	false);
		return;
	}
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	//	Однозначное определение что планшет
	$pads	= 'ipad|xoom|sch-i800|playbook|tablet|kindle';	
	if (preg_match("#$pads#", $agent) || testValue('tablet')){
		define('isTablet',	true);
		define('isPhone',	false);
		return;
	}
	//	Однозначное определение что телефон
	$phones	= 'iphone|ipod|blackberry|opera\smini|windows\sce|palm|smartphone|iemobile|nokia|series60|midp|mobile';	
	if (preg_match("#$phones#", $agent) || testValue('phone'))
	{
		define('isTablet',	false);
		define('isPhone',	true);
		return;
	}
	//	Возможно планшет
	$pads	= 'android';	
	define('isTablet', preg_match("#$pads#", $agent));
	define('isPhone', false);
}
function isPhone(){
	if (defined('isPhone')) 	return isPhone;
	deviceDetect();
	return isPhone;
}
function isTablet()
{
	if (defined('isTablet')) 	return isTablet;
	deviceDetect();
	return isTablet;
}
function devicePrefix()
{
	if (isPhone())	return 'phone_';
	if (isTablet())	return 'tablet_';
}
///////////////////////////////////////
//	CONFIG - flat store array for any session data
class config
{
	static $_CONFIG = array();
	static function all(){
		return config::$_CONFIG;
	}
	static function get($key, $default = NULL){
		$val = config::$_CONFIG[$key];
		return $val?$val:$default;
	}
	static function set($key, $value){
		config::$_CONFIG[$key] = $value;
	}
};
///////////////////////////////////////
//	META - deep inherit flat store array for any session data
class meta
{
	static $_META = array();
	static function all(){
		return meta::$_META;
	}
	static function begin($sets = NULL)
	{
		$ix 	= count(meta::$_META);
		$data	= $ix?meta::$_META[$ix-1]:array();

		if (is_array($sets)) $data = array_merge($data, $sets);
		meta::$_META[$ix] = $data;
	}
	static function end(){
		array_pop(meta::$_META);
	}
	static function get($key){
		$ix = count(meta::$_META);
		if ($ix) return  meta::$_META[$ix-1][$key];
	}
	static function set($key, $value){
		$ix = count(meta::$_META);
		if ($ix) meta::$_META[$ix-1][$key] = $value;
	}
};
///////////////////////////////////////
//	STACK - stacked data store for any session data with single access
class stack
{
	static $_STACK = array();
	static function all(){
		return stack::$_STACK;
	}
	static function count(){
		return count( stack::$_STACK);
	}
	static function push($data){
		stack::$_STACK[] = $data;
	}
	static function pop(){
		return array_pop(stack::$_STACK);
	}
	static function get(){
		return stack::$_STACK[count(stack::$_STACK)-1];
	}
};
///////////////////////////////////////
//	STACK - stacked data store for any session data with single access
class initialize
{
	static function siteInitialize()
	{
		self::globalInitialize();
		self::localInitialize();
	}
	static function globalInitialize()
	{
		global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
		//////////////////////
		//	Загрузить глобальный кеш
		$_GLOBAL_CACHE_NEED_SAVE	= false;
		$_GLOBAL_CACHE				= unserialize(file_get_contents(globalCacheFolder.'/globalCache.txt'));
		if (!$_GLOBAL_CACHE) $_GLOBAL_CACHE = array();
		
		$ini	= getGlobalCacheValue('ini');
		if (is_array($ini))
		{
			createMemCache($ini);
		}else{
			$ini = readIniFile(configName);
			setGlobalCacheValue('ini', $ini);
			
			createMemCache($ini);
			memClear('', true);
		}
		//	Найти физический путь корня сайта
		$globalRootURL	= $ini[':']['globalRootURL'];
		if (!$globalRootURL){
			$globalRootURL	= substr($_SERVER['PHP_SELF'], 0, -strlen(basename($_SERVER['PHP_SELF'])));
			$ini[':']['globalRootURL']	= $globalRootURL;
			setGlobalIniValues($ini);
		}
		//	like /dev	- Путь относительно корня WEB хостинга
		$globalRootURL	= rtrim($globalRootURL, '/');
		define('globalRootURL',	$globalRootURL);
		//	like /www/dev- Пуь относительно файловой системы
		define('globalRootPath',str_replace('\\' , '/', dirname(__FILE__)));
		
		//	Задать константы путей для текущего сайта
		define('localRootURL',		globalRootURL.'/'.sitesBase.'/'.siteFolder()); 
		define('localRootPath',		sitesBase.'/'.siteFolder());
		define('localConfigName',	localRootPath.'/'.modulesBase.'/config.ini');
	
		define('cacheRoot',			globalCacheFolder.'/'.siteFolder());
		define('cacheRootPath',		cacheRoot . '/'. localSiteFiles);
	}
	static function localInitialize()
	{
		$timeStart		= getmicrotime();
		createCache();
		$timeCache		= round(getmicrotime() - $timeStart, 4);
		//////////////////////
		//	Задать локальные конфигурационные данные для сесстии
		$compileFile	= cacheRoot.'/'.localCompiledCode;
		$ini			= getCacheValue('ini');
		if ($ini && $ini[':']['checkCompileFiles'] && !checkCompileFiles())
			$ini = NULL;
	
		if (!is_array($ini) || !is_file($compileFile))
		{
			compileFiles(cacheRoot);
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
			m('message:trace:',	"$timeCache cache read $cacheFile");
			m("message:trace", 	"$time Included $compileFile file");
		}
	}
};
//	site tools
function getSiteFile($path)
{
	$file	= localRootPath . '/' . $path;
	if (is_file($file)) return $file;
	$file	= cacheRootPath . '/' . $path;
	if (is_file($file)) return $file;
	if (is_file($path)) return $path;
}
function writeSiteFile($path, $data){
	$path = localRootPath . '/' . $path;
	makeDir(dirname($path));
	return file_put_contents_safe($path, $data);
}
function getSiteFiles($path, $filter='')
{
	if (!is_array($path))
		return getFiles(array(
			localRootPath . '/' . $path,
			cacheRootPath . '/' . $path
		), $filter);

	$paths	= array();
	foreach($path as $path){
		$paths[]	= localRootPath . '/' . $path;
		$paths[]	= cacheRootPath . '/' . $path;
	}
	return getFiles($paths, $filter);
}
?>