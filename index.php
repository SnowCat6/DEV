<?
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//	apd_set_pprof_trace();
//	Засечем время начала работы
define('sessionTimeStart',	getmicrotime());
define('sessionID', 		userIP().':'.sessionTimeStart);

define('modulesBase',	'_modules');
define('templatesBase',	'_templates');
define('sitesBase',		'_sites');
define('configName',	'_sites/config.ini');

global $_CONFIG;
$_CONFIG = array();

//	Console run
if (defined('STDIN'))
{
	switch($argv[1]){
	//	Recompile changed files and cleanup cache
	case 'clearCache':
		$site	= $argv[2];
		if (!$site) return;
		
		echo "Clearing cache $site";
		executeCron($site, '/');
		globalInitialize();
		compileFiles();
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
		clearCacheCode(true);
		flushCache(true);
		memClear();
		return;
	//	Cron's tasks tick
	default:
		if (count($argv) == 2 || count($argv) == 3){
			$site	= $argv[1];
			$url	= $argv[2];
			if (!$url) $url = '/cron_synch.htm';
			echo "Run cron $site$url\r\n";
			executeCron($site, $url);
			break;
		}else
		if (count($argv) == 1){
			echo "Run sites cron\r\n";
			if (!cronTick($argv)) return;
			break;
		}else return;
	}
}

header('Content-Type: text/html; charset=utf-8');
$renderedPage	= NULL;
$pageCacheName	= NULL;

//	Если запущен на старой версии PHP то определим недостающую функцию
if (!function_exists('file_put_contents')){
	function file_put_contents($name, &$data){
		$f = fopen($name, 'w'); $bOK = fwrite($f,$data); fclose($f);
		return $bOK;
	}
}
//////////////////////
//	Инициализация данных, глобальный и локальный кеш, задание констант
ob_start();
globalInitialize();
localInitialize();
ob_end_clean();

//////////////////////
//	MAIN CODE
//////////////////////
if (!defined('_CRON_'))
{
	if ((int)ini_get('max_execution_time') > 60) set_time_limit(60);
	$ini		= getCacheValue('ini');
	$template	= $ini[getRequestURL()]['template'];
	if (!$template) $template	= $ini[':']['template'];
	if (!$template) $template	= 'default';
	
	$_CONFIG['page']['template']	= "page.$template";
}
$_CONFIG['page']['renderLayout']	= 'body';
$_CONFIG['noCache']					= 0;

//	Запуск сайта, обработка модулей вроде аудентификации пользователя
event('site.start', $_CONFIG);

//	Full page cache
event('site.getPageCacheName', $pageCacheName);
if ($pageCacheName) $renderedPage = memGet($pageCacheName);

//	Render page
if (is_null($renderedPage))
{
	ob_start();
	//	Вывести страницу с текущем URL
	renderPage(getRequestURL());
	//	Получить буффер вывода для обработки
	$renderedPage .= ob_get_clean();
	if ($pageCacheName && !defined('noPageCache')){
		memSet($pageCacheName, $renderedPage);
	}
}
//	$renderedPage .= getmicrotime() - sessionTimeStart;
//	Завершить все выводы на экран
//	Возможна постобработка страницы
event('site.end',	$renderedPage);
//	Обработчики GZIP и прочее
event('site.close',	$renderedPage);
//	Вывести в поток
echo $renderedPage;
//	Вывести все буффера
flush();
//	Постобработка, фоновые процессы, без вывода на экран
event('site.exit',	$_CONFIG);

//////////////////////
//	FINAL AND CLEANUP
flushCache();
flush();

////////////////////////////////////
//	tools
////////////////////////////////////
function redirect($url){
	flushCache();
	ob_clean();
	module('cookie');
	$server = $_SERVER['HTTP_HOST'];
	header("Location: http://$server$url");
	die;
}
function setNoCache(){
	$GLOBALS['_CONFIG']['noCache']++;
}
function getNoCache(){
	return $GLOBALS['_CONFIG']['noCache'];
}

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

function getValueEncode()
{
	if (defined('ValueEncode')) return ValueEncode;
	foreach(getallheaders() as $name => $val){
		if (strtolower($name) != 'content-type') continue;
		if (!preg_match('#charset\s*=\s*(.+)#i', $val, $v)) break;
		define('ValueEncode', $v[1]);
		return ValueEncode;
	}
	define('ValueEncode', NULL);
	return ValueEncode;
}

function getValue($name)
{
	$val = $_POST[$name];
	if (!$val) $val = $_GET[$name];
	removeSlash($val);
	return $val;
}

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
	foreach($path as $name){
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
	makeDir(dirname($file));
	return file_put_contents($file, $value, LOCK_EX) != false;
}

//	Получить список файлов по фильтру
function getFiles($dir, $filter = '')
{
	if (is_array($dir)){
		$res = array();
		foreach($dir as $path){
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

//	Копировать всю папку
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


function hashData(&$value){
	if (!is_array($value)) return md5($value);

	$hash = '';
	foreach($value as $key => &$val){
		$hash = md5($hash.$key.hashData($val));
	}
	return $hash;
}

///	Выполнить функцию по заданному названию, при необходимости подгрузить из файла
function module($fn, $data = NULL){
	return moduleEx($fn, $data);
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
//	$event	= getCacheValue('localEvent');
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

//	Добавить обработчки URL тсраницы
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
	setCacheValue('localSnippets', $localSnippets);
}

///	Обработать страницу по заданному URL и вывести в стандартный вывод
function renderPage($requestURL)
{
	$config			= &$GLOBALS['_CONFIG'];
	event('site.renderStart', $config);
	$renderedPage	= renderURL($requestURL);
	$template		= $config['page']['template'];

	//	Загрузка страницы
	$pages		= getCacheValue('pages');
	if (isset($pages[$template])){
		$config['page']['layout'][$config['page']['renderLayout']] = $renderedPage;
		include($pages[$template]);
		m("message:trace", "Included $pages[$template] file");
	}else{
		echo $renderedPage;
		event('site.noTemplateFound', $config);
		module('message:url:error', "Template not found '$template'");
	}
	event('site.renderEnd', $config);
	return true;
}

//	Вызвать обработчик URL и вернуть результат как строку
function renderURL($requestURL)
{
	$parseResult = renderURLbase($requestURL);
	//	Если все получилось, возыращаем результат
	if (isset($parseResult)) return $parseResult;

	//	Страница не найдена, но не все потеряно, возможно есть событийный обработчик
	ob_start();
	event('site.noUrlFound', $requestURL);
	$parseResult = ob_get_clean();
	//	Если все получилось, возыращаем результат
	if ($parseResult) return $parseResult;
	
	//	Увы, действительно страницы не  найдено
	ob_start();
	event('site.noPageFound', $requestURL);
	$parseResult = ob_get_clean();
	if ($parseResult) return $parseResult;
	
	module('message:url:error', "Page not found '$requestURL'");
	return NULL;
}
//	Найти обработчик URL и вернуть страницу
function renderURLbase($requestURL)
{
	global $_CACHE;
	$parseRules	= &$_CACHE['localURLparse'];
	//	Поищем обработчик URL
	$pageRender	= NULL;
	foreach($parseRules as $parseRule => &$parseModule)
	{
		if (!preg_match("#^/$parseRule(\.htm$)#iu", $requestURL, $parseResult)) continue;
		//	Если найден, то выполняем
		unset($parseResult[count($parseResult)-1]);
		$pageRender = mEx($parseModule, $parseResult);
		//	Если все получилось, возвращаем результат
		if ($pageRender) return $pageRender;
	}
	return $pageRender;
}

//	Получить указатель на функцию, при необходимости подгрзить файл
function getFn($fnName)
{
	if (function_exists($fnName)) return $fnName;

	global $_CACHE;
	$templates	= &$_CACHE['templates'];//getCacheValue('templates');
	$template	= $templates[$fnName];
	if (!$template) return NULL;

	$timeStart	= getmicrotime();
	include_once($template);
	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $template file");
	if (function_exists($fnName)) return $fnName;
	
	module('message:fn:error', "Function not found '$fnName'");
	return NULL;
}

//	Прлучить запрашиваемый URL
function getRequestURL()
{
	$url	= $_GET['URL'];
	if ($url) return "/$url";
	
	$url	= $_SERVER['REQUEST_URI'];
	$url	= substr($url, strlen(globalRootURL));
	return preg_replace('@[#?].*@', '', $url);
}

///////////////////////////////////////////
//	Функции инициализации данных
///////////////////////////////////////////

//	Задать глобальные конфигурационные данные для сесстии
function globalInitialize()
{
	global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
	//////////////////////
	//	Загрузить глобальный кеш
	define('globalCacheFolder', '_cache');
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
	}
	//	like /dev
	$globalRootURL	= rtrim($globalRootURL, '/');
	define('globalRootURL',	$globalRootURL);
	//	like /www/dev
	define('globalRootPath',str_replace('\\' , '/', dirname(__FILE__)));
	
	//////////////////////
	define('localHost',			getSiteURL());
	define('localHostPath',		getSitePath(localHost));
	define('localRootURL',		getSiteURL());
	define('localRootPath',		getSitePath(localHost));

	define('localCacheFolder',	'_cache/'.localHost);
	define('localCompiledCode', 'modules.php');
	define('localCompilePath',	'compiledPages');
	define('localSiteFiles',	'siteFiles');
	define('localConfigName',	localRootPath.'/_modules/config.ini');
	
	if (!$bCacheExists){
		memClear('', true);
	}
}

//	Задать локальные конфигурационные данные для сесстии
function localInitialize()
{
	global $_CACHE_NEED_SAVE, $_CACHE;
	
	if (strncmp('http://', localHost, 7) == 0){
		ob_clean();
		header("Location: " . localHost);
		die;
	}
	//	Загрузить локальный кеш
	$_CACHE_NEED_SAVE	= false;
	$cacheFile			= localCacheFolder.'/cache.txt';
	define('cacheFileTime', filemtime($cacheFile));
	$_CACHE				= readData($cacheFile);
	if (!$_CACHE) $_CACHE = array();

	//////////////////////
	//	Задать локальные конфигурационные данные для сесстии
	$ini	= getCacheValue('ini');
	if (!is_array($ini))
	{
		$ini	= compileFiles();
	}else{
		//	Задать путь хранения изображений
		define('images', getCacheValue('localImagePath'));

		//	При необходимости вывести сообщения от модулей в лог
		$timeStart	= getmicrotime();
		ob_start();
		include_once(localCacheFolder.'/'.localCompiledCode);
		module('message:trace:modules', trim(ob_get_clean()));
		$time 		= round(getmicrotime() - $timeStart, 4);
		m("message:trace", "$time Included ".localCompiledCode." file");
	}

	if (defined('memcache')){
		m("message:trace", "Use memcache");
	}

	$timeStart		= getmicrotime();
	$compiledPath	= localCacheFolder.'/'.localCompilePath.'/compiled.php3';
	include_once($compiledPath);
	$time 			= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included $compiledPath file");
}

function compileFiles($localCacheFolder)
{
	global $_CACHE;
	if (!$localCacheFolder) $localCacheFolder = localCacheFolder;
	
	$_CACHE		= array();
	$ini 		= readIniFile(localConfigName);
	setCacheValue('ini', $ini);

	//	Initialize image path
	$localImagePath = $ini[':images'];
	if (!$localImagePath) $localImagePath = localHostPath.'/images';
	setCacheValue('localImagePath', $localImagePath);
	//	Задать путь хранения изображений
	if (!defined('images')) define('images', $localImagePath);

//	Initialize event array
	$localEvent = array();
	setCacheValue('localEvent', $localEvent);
	
	//	Access rule parse
	$userAccess = array();
	setCacheValue('localAccessParse', $userAccess);

	//	Initialize url parse values
	$localURLparse = $ini[':URLparse'];
	if (!is_array($localURLparse)) $localURLparse = array();
	setCacheValue('localURLparse', $localURLparse);

	$gini	= getGlobalCacheValue('ini');
	$host	= getSiteURL();

	$enable		= $ini[":enable"];
	if (!is_array($enable))	$enable = array();

	$packages	= $ini[":packages"];
	if (!is_array($packages))$packages = array();

	modulesConfigure($localCacheFolder, $enable, $packages);
	
	//	При необходимости вывести сообщения от модулей в лог
	$timeStart	= getmicrotime();
	ob_start();
	include_once($localCacheFolder.'/'.localCompiledCode);
	module('message:trace:modules', trim(ob_get_clean()));
	$time 		= round(getmicrotime() - $timeStart, 4);
	m("message:trace", "$time Included ".localCompiledCode." file");
	
	//	Initialize pages and copy desing files
	$localPages = array();
	pagesInitialize(modulesBase,	$localPages, $enable);
	pagesInitialize(templatesBase,	$localPages, $enable);
	foreach($packages as $path)	pagesInitialize($path,		$localPages, $enable);
	pagesInitialize(localHostPath,	$localPages, $enable);

	$bOK	= pageInitializeCopy($localCacheFolder.'/'.localSiteFiles, 		$localPages);
	$bOK	&=	pageInitializeCompile($localCacheFolder,	$localPages);
	if ($bOK)	setCacheValue('pages', $localPages);
	else echo 'Error copy design files';
	
	$ini	= getCacheValue('ini');
	event('config.end', $ini);
	
	if (!file_exists('.htaccess')) htaccessMake();
	
	return $ini;
}

function modulesConfigure($localCacheFolder, &$enable, &$packages)
{
	$compiledPath	= $localCacheFolder.'/'.localCompiledCode;
	//	Initialize modules and templates
	$localModules	= array();
	modulesInitialize(modulesBase,	$localModules, $enable);
	modulesInitialize(templatesBase,$localModules, $enable);
	foreach($packages as $path) modulesInitialize($path,$localModules, $enable);
	modulesInitialize(localHostPath.'/'.modulesBase,	$localModules, $enable);

	$maxModifyTime = 0;
	foreach($localModules as $modulePath){
		$maxModifyTime = max($maxModifyTime, filemtime($modulePath));
	}

	if ($maxModifyTime > filemtime($compiledPath)){
		//	Загрузить все оставшиеся модули
		ob_start();
		foreach($localModules as $name => $modulePath){
			echo "<? // Module $name loaded from  $modulePath ?>\r\n";
			readfile($modulePath);
			echo "\r\n";
		};
		$bOK = file_put_contents_safe($compiledPath, ob_get_clean());
		$modulesPath	= $localCacheFolder.'/'.localSiteFiles;
		$bOK&= pageInitializeCopy($modulesPath, $localModules);

		if (!bOK){
			echo 'Error write compiled modules';
			die;
		};
	}
	setCacheValue('modules', $localModules);
}
//	Поиск всех загружаемых модуле  и конфигурационных програм
function modulesInitialize($modulesPath, &$localModules, &$enable)
{
	$module = basename($modulesPath);
	if (isset($enable[$module])) return;
	//	Поиск конфигурационных файлов
	$configFiles	= getFiles($modulesPath, '^config\..*php$');
	foreach($configFiles as $configFile){
		include_once($configFile);
	}

	//	Поиск модулей
	$files	= getFiles($modulesPath, '^module_.*php$');
	foreach($files as $name => $path){
			// remove ext
		$name = preg_replace('#\.[^.]*$#',		'', $name);
		$localModules[$name] = $path;
	}
	
	$dirs = getDirs($modulesPath, '^_');
	foreach($dirs as $modulePath){
		//	Сканировать поддиректории
		modulesInitialize($modulePath, $localModules, $enable);
	};
}

//	Поиск всех страниц и шаблонов
function pagesInitialize($pagesPath, &$pages, &$enable)
{
	$module = basename($pagesPath);
	if (isset($enable[$module])) return;

	//	Поиск страниц сайта
	$files	= getFiles($pagesPath, '^(page\.|phone\.page\.|tablet\.page\.|template\.)');
	foreach($files as $name => $path){
		$name = preg_replace('#\.[^.]*$#', '', $name);
		$pages[$name] = $path;
	}

	$dirs = getDirs($pagesPath, '^_');
	foreach($dirs as $pagePath){
		//	Сканировать поддиректории
		pagesInitialize($pagePath, $pages, $enable);
	};
}

//	Копирование всех дизайнерских файлов из модуля в основной каталог сайта, за исключением системных файлов
function pageInitializeCopy($rootFolder, $pages)
{
	$bOK = true;
	makeDir($rootFolder);
	foreach($pages as $pagePath)
	{
		$baseFolder	= dirname($pagePath);

		//	Копирование файлов
		$files 	= getFiles($baseFolder);
		foreach($files as $name => $sourcePath)
		{
			if (preg_match('#^(page\.|.*\.page\.)#', $name)) continue;
			if (preg_match('#^(module_|config\.|template\.)#', $name)) continue;

			$destPath = "$rootFolder/$name";
			if ($sourcePath == $destPath) continue;
			if (filemtime($sourcePath) == filemtime($destPath)) continue;

			if (!copy($sourcePath, $destPath)){
				$bOK = false;
				continue;
			}
			touch($destPath, filemtime($sourcePath));
		};
		
		//	Копирование папок
		$dirs		= getDirs($baseFolder, '^[^_].+');
		foreach($dirs as $name => $sourcePath)
		{
			if (is_int(strpos($sourcePath, images))) continue;
			$bOK &= copyFolder($sourcePath, "$rootFolder/$name");
		}
	};
	return $bOK;
}

//	Compile pages
function pageInitializeCompile($localCacheFolder, &$pages)
{
	$templates			= array();
	$comiledTemplates	= array();
	$compiledTmpName	= "$localCacheFolder/".localCompilePath."/compiled.php3";
	$compiledFileName	= localCacheFolder."/".localCompilePath."/compiled.php3";
	$comiledFileTime	= NULL;

	foreach($pages as $name => &$pagePath)
	{
		$fileName	= basename($pagePath);
		if (strpos($fileName, ".php3") && preg_match('#^template\.#', $name))
		{
			$name					= preg_replace('#^template\.#', '', $name);
			$templates[$name]		= $compiledFileName;
			$comiledTemplates[$name]= $pagePath;
			$comiledFileTime		= max($comiledFileTime, filemtime($pagePath));
			$pagePath 				= $compiledFileName;
			continue;
		}

		$compiledPagePath	= "$localCacheFolder/".localCompilePath."/$fileName";
		if (filemtime($pagePath) != filemtime($compiledPagePath))
		{
			$compiledPage		= file_get_contents($pagePath);
			event('page.compile', $compiledPage);
			
			if (!$compiledPage) continue;
			if (!file_put_contents_safe($compiledPagePath, $compiledPage)) return false;
			touch($compiledPagePath, filemtime($pagePath));
		}
		
		$pagePath = localCacheFolder."/".localCompilePath."/$fileName";
		if (preg_match('#^template\.#', $name)){
			$name				= preg_replace('#^template\.#', '', $name);
			$templates[$name]	= $pagePath;
		}
	}
	
	if ($comiledFileTime > filemtime($compiledTmpName))
	{
		$compiledTemplate	= '';
		foreach($comiledTemplates as $name => &$pagePath)
		{
			$compiledPage		= file_get_contents($pagePath);
			event('page.compile', $compiledPage);
			$compiledTemplate	.= "<? //	Template $name loaded from  $pagePath ?>\r\n";
			$compiledTemplate	.=$compiledPage;
		}
		file_put_contents_safe($compiledTmpName, $compiledTemplate);
	}

	setCacheValue('templates', $templates);
	return true;
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
	
	$sites		= getGlobalCacheValue('HostSites');
	if (!is_array($sites)){
		$sites = getDirs('_sites');
		if (!$sites) $sires = array();
		setGlobalCacheValue('HostSites', $sites);
	}

	$siteURL	= $_SERVER['HTTP_HOST'];
	$siteURL	= preg_replace('#^www\.#', '', $siteURL);
	
	$ini		= getGlobalCacheValue('ini');
	$sitesRules	= $ini[':globalSiteRedirect'];
	if (is_array($sitesRules))
	{
		foreach($sitesRules as $rule => $host){
			if (preg_match("#$rule#i", $siteURL)){
				define('siteURL', $host);
				return siteURL;
			}
		}
	}

	if (count($sites) != 1) define('siteURL', 'default');
	else{
		list($url) = each($sites);
		define('siteURL', $url);
	}
	return siteURL;
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
	if (defined('clearCacheCode'))	return clearCacheCode(true);
	if (defined('clearCache'))		return clearCache(true);

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
function clearCacheCode($bClearNow = false)
{
	if ($bClearNow){
		if (defined('STDIN'))
		{
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
			return;
		}
		$site	= getSiteURL();
		return execPHP("index.php clearCacheCode $site");
	}
	define('clearCacheCode', true);
}
function clearCache($bClearNow = false)
{
	if ($bClearNow){
		$site	= getSiteURL();
		return execPHP("index.php clearCache $site");
	}
	
	if (defined('clearCache')) return;
	define('clearCache', true);
}

//	add		=> doc:page:article
//	add		=> doc:57:article
//	write	=> doc:57
//	restore	=> backup:restoreFolderName
function access($val, $data)
{
	$bOK		= false;
	$cache		= &$GLOBALS['_CACHE'];
	$parseRules	= &$cache['localAccessParse'];
	foreach($parseRules as $parseRule => &$access)
	{
		if (!preg_match("#^$parseRule$#", $data, $v)) continue;
		foreach($access as &$parseModule){
			if (moduleEx("$parseModule:$val", $v)){
				return true;
			}
		}
	}
	return $bOK;
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
	"AddDefaultCharset UTF-8\r\n\r\n".
	"ErrorDocument 404 /pageNotFound404\r\n".
	"RewriteEngine On\r\n".
	"RewriteRule (.+)\.htm$	$globalRootURL/index.php\r\n".
	"RewriteRule ^([^.]+)$	$globalRootURL/index.php\r\n".
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
/*
	$ctx	= preg_replace("/# <= index2.*# => index2/s", '', $ctx);
	$ctx	.= "\r\n".
	"# <= index\r\n".
	"RewriteCond %{REQUEST_FILENAME} !-f\r\n".
	"RewriteRule .*	$globalRootURL/index.php	[QSA]\r\n".
	"# => index2\r\n";
*/	
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
			"RewriteCond %{REQUEST_FILENAME} /$localImagePath\r\n".
			"RewriteRule ^($localImagePath/.+)	$globalRootURL/_sites/$hostName/$1\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_FILENAME} !/_|php$\r\n".
			"RewriteRule (.+)	_cache/$hostName/siteFiles/$1\r\n".
		
			"RewriteCond %{HTTP_HOST} $hostRule\r\n".
			"RewriteCond %{REQUEST_FILENAME} _editor/.*(fck_editorarea.css|fckstyles.xml)\r\n".
			"RewriteCond $globalRootPath/_cache/$hostName/siteFiles/%1 -f\r\n".
			"RewriteRule .*	_cache/$hostName/siteFiles/%1"
		;
	}
	
	$ctx	.= "\r\n".
		"# <= $safeName\r\n".
		"$c\r\n".
		"# => $safeName\r\n";
}

function userIP(){
	return GetIntIP($_SERVER['REMOTE_ADDR']);
}
//	Получить адрес клиента
function GetIntIP($src){
  $t = explode('.', $src);
  return count($t) != 4 ? 0 : 256 * (256 * ((float)$t[0] * 256 + (float)$t[1]) + 
    (float)$t[2]) + (float)$t[3];
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

	@$d		= opendir($dir);
	if (!$d) return;
	
	while(($file = readdir($d)) != null){
		if ($file == '.' || $file == '..') continue;
		$file = "$dir/$file";
		if (is_file($file))	unlink($file);
		else
		if (is_dir($file)) delTree($file, true, false);
	}
	@closedir($d);
	if ($bRemoveBase || $bUseRename) @rmdir($dir);
}

function cronTick(&$argv)
{
	chdir(dirname(__FILE__));
	$cronLock	= "_cache/cron.txt";
	$cronLog	= "_cache/cron.log";
	
	//	Делаем через блокировочный файл, чтобы никогда не запустить 2 копии шедулера.
	if (is_file($cronLock) && time() - filemtime($cronLock) < 30*60*60)
	file_put_contents($cronLock, '');

	$fLog		= fopen($cronLog, 'w');
	$ini		= readIniFile(configName);
	$cron		= $ini[':cron'];
	$cronURL	= $cron['cronURL'];

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
	case 'Windows':
		return "php.exe $path";
	case 'Linux':
		return "php $path";
	case 'OSX':
	case 'FreeBSD':
		$php = PHP_BINDIR;
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

function executeCron($host, $url)
{
	define('_CRON_', true);
	define('siteURL',$host);
	$_SERVER['REQUEST_URI'] = $url;
	return true;
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
	$f		= "#^$url:$filter#";
	$f2		= "#^$url:#";

	$allSlabs	= $memcacheObject->getExtendedStats('slabs');
	$items		= $memcacheObject->getExtendedStats('items');
	foreach($allSlabs as $server => &$slabs) {
		foreach($slabs AS $slabId => &$slabMeta) {
			if (!is_int($slabId)) continue;
			$cdump = $memcacheObject->getExtendedStats('cachedump', $slabId);
			foreach($cdump AS $keys => &$arrVal) {
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
?>
