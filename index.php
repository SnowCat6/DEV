<?
//	Засечем время начала работы
define('sessionTimeStart', getmicrotime());

define('modulesBase',	'_modules');
define('templatesBase',	'_templates');
define('configBase',	modulesBase);
define('configName',	configBase.'/config.ini');

//	Если запущен на старой версии PHP то определим недостающую функцию
if (!function_exists('file_put_contents')){
	function file_put_contents($name, &$data){
		@$f = fopen($name, 'w'); @$bOK = fwrite($f,$data);	@fclose($f);
		return $bOK;
	}
}

ob_start();
$_CONFIG = array();

//////////////////////
globalInitialize();
localInitialize();

//////////////////////
//	MAIN CODE
//////////////////////
event('site.start', $_CONFIG);

renderPage(getRequestURL(), $_CONFIG);
$renderedPage = ob_get_clean();

event('site.end',	$renderedPage);
event('site.close',	$renderedPage);
echo $renderedPage;

//////////////////////
//	FINAL AND CLEANUP
flushCache();

////////////////////////////////////
//	tools
////////////////////////////////////
function redirect($url){
	ob_clean();
	module('cookie');
	$server = $_SERVER['HTTP_HOST'];
	header("Location: http://$server$url");
	die;
}

function setTemplate($template){
	$GLOBALS['_CONFIG']['page']['template'] = "page.$template";
}

//	set multiply values int local site config file
function setIniValues($data)
{
	$ini = readIniFile(localHostPath."/".configName);
	if (hashData($data) == hashData($ini)) return true;

	if (!writeIniFile(localHostPath."/".configName, $data)) return false;

	dataMerge($data, getGlobalCacheValue('ini'));
	setCacheValue('ini', $data);

	$oldCache = $ini[':']['useCache']	== 1;
	$newCache = $data[':']['useCache']	== 1;
	if ($oldCache != $newCache && !$newCache) clearCache();

	return true;
}

//	set multiply values int local site config file
function setGlobalIniValues($data)
{
//	$ini = readIniFile(configName);
//	if (hashData($data) == hashData($ini)) return true;

	if (!writeIniFile(configName, $data)) return false;

	setGlobalCacheValue('ini', $data);

	@unlink(globalCacheFolder.'/globalCache.txt');
	clearCache();

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
	@$val = $_POST[$name];
	if (!$val) @$val = $_GET[$name];
	removeSlash($val);
	return $val;
}

//	Удалить квотирование
function removeSlash(&$var)
{
	if (!get_magic_quotes_gpc()) return;
	if (is_array($var)){
		while(list($ndx,)=each($var)) removeSlash($var[$ndx]);
		reset($var);
	}else $var = stripslashes($var);
}

function testValue($name){
	return isset($_POST[$name]) || isset($_GET[$name]);
}

function makeQueryString($data, $name = '', $bNameEncode = true)
{
	if ($bNameEncode) $name = urlencode($name);
	if (!is_array($data)) return $name?"$name=$data":$data;

	$v = '';
	foreach($data as $n => &$val)
	{
		if ($v) $v .= '&';
		$n = urlencode($n);
		
		if (is_array($val)){
			$v .= makeQueryString($val, $name?$name."[$n]":$n, false);
		}else{
			if (!preg_match('#^\d+$#', $n)){
				$val = urlencode($val);
				$v  .= $name?$name."[$n]=$val":"$n=$val";
			}else{
				$v  .= $name?$name."[]=$val":"$val";
			}
		}
	}
	return $v;
}

//	Возвращает время до принудительного закрытия сессии сервером, в секундах
function sessionTimeout(){
	if (connection_aborted()) return  0;
	
	$maxTime	= (int)ini_get('max_execution_time');	//	seconds
	return $maxTime - (getmicrotime() - sessionTimeStart);
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
	foreach($path as $name)	{
		@mkdir($dir .= "$name/");
		@chmod($dir, 0777);
	}
}

//	Применить к файлу права доступа, на некоторых хостингах иначе все работает плохо
function fileMode($path){
	makeDir(dirname($path));
	chmod($path, 0666);
}

// записать гарантированно в файл, в случае неудачи старый файл остается
function file_put_contents_safe($file, &$value)
{
	return file_put_contents($file, $value, LOCK_EX) != false;
	
	$tmpFile = "$file.tmp";
	$bakFile = "$file.bak";
	@unlink($bakFile);
	@unlink($tmpFile);

	makeDir(dirname($file));
	if (file_put_contents($tmpFile, $value, LOCK_EX) || $value == ''){
		if (!is_file($file) || @rename($file, $bakFile)){
			if (@rename($tmpFile, $file)){
				@unlink($bakFile);
				return true;
			}
			@unlink($file);
			@unlink($tmpFile);
			@rename($bakFile, $file);
		}
	}
	@unlink($tmpFile);
	return false;
}

//	Получить список файлов по фильтру
function getFiles($dir, $filter = '')
{
	$files	= array();
	@$d		= opendir($dir);
	while((@$file = readdir($d)) != false)
	{
		if ($file=='.' || $file=='..') continue;
		$f = "$dir/$file";
		if ($filter && !preg_match("#$filter#i", $file)) continue;
		if (!is_file($f)) continue;
		$files[$file] = $f;
	}
	@closedir($d);
	ksort($files);
	return $files;
}

//	Получить список каталогов по фильтру
function getDirs($dir, $filter = ''){
	$files	= array();
	@$d		= opendir($dir);
	while((@$file = readdir($d)) != false)
	{
		if ($file=='.' || $file=='..') continue;
		$f = "$dir/$file";
		if (!is_dir($f)) continue;
		if ($filter && !preg_match("#$filter#i", $file)) continue;
		$files[$file] = $f;
	}
	@closedir($d);
	ksort($files);
	return $files;
}

//	Копировать всю папку
function copyFolder($src, $dst, $excludeFilter = '')
{
	if ($src == $dst) return true;
	makeDir($dst);

	$bOK	= true;
	$d		= opendir($src);
	while($file = @readdir($d))
	{
		if ($excludeFilter && preg_match("#$excludeFilter#", $file)) continue;
		if ($file=='.' || $file=='..') continue;
		
		$source = "$src/$file";
		$dest	=  "$dst/$file";
		if (is_dir($source))
		{
			$bOK &= copyFolder($source, $dest, $excludeFilter);
		}else{
			if (filemtime($source) == @filemtime($dest))continue;
			if (!@copy($source, $dest)) $bOK = false;
			@touch($dest, filemtime($source));
		}
	}
	closedir($d);
	return $bOK;
}


//	Объеденить массивы
function dataMerge(&$dst, $src)
{
	if (!is_array($src)) return;
	foreach($src as $name => &$val)
	{
		if (is_array($val)){
			if (isset($dst[$name])) dataMerge($dst[$name], $val);
			else $dst[$name] = $val;
		}else{
			if (!isset($dst[$name])) $dst[$name] = $val;
		}
	}
}
function hashData(&$value){
	if (is_array($value)){
		$hash = '';
		foreach($value as $key => &$val){
			$hash = md5($hash.$key.hashData($val));
		}
		return $hash;
	}else
	return md5($value);
}

//	вызвать событие для всех обработчиков
function event($eventName, &$eventData)
{
	$event	= getCacheValue('localEvent');
	@$ev	= $event[$eventName];
	if (!$ev) return;
	
	foreach($ev as $module){
		module($module, &$eventData);
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
	$localAccessParse[$parseRule]	= $parseModule;
	setCacheValue('localAccessParse', $localAccessParse);
}

//	roles
function addRole($roleName, $roleAccess){
	$localUserRoles = getCacheValue('localUserRoles');
	$localUserRoles[$roleAccess]	= $roleName;
	setCacheValue('localUserRoles', $localUserRoles);
}

///	Обработать страницу по заданному URL и вывести в стандартный вывод
function renderPage($requestURL, &$config)
{
	event('site.renderStart', &$config);
	$renderedPage = renderURL($requestURL);
	$template	= $config['page']['template'];

	//	Загрузка страницы
	$pages		= getCacheValue('pages');
	if (isset($pages[$template])){
		$config['page']['layout'][@$config['page']['renderLayout']] = $renderedPage;
		include_once($pages[$template]);
	}else{
		echo $renderedPage;
		event('site.noTemplateFound', $config);
	}
	event('site.renderEnd', $config);
	return true;
}

//	Вызвать обработчик URL и вернуть результат как строку
function renderURL($requestURL)
{
	$parseResult = renderURLbase($requestURL);
	//	Если все получилось, возыращаем результат
	if ($parseResult != NULL) return $parseResult;

	//	Страница не найдена, но не все потеряно, возможно есть событийный обработчик
	ob_start();
	event('site.noUrlFound', $requestURL);
	$parseResult = ob_get_clean();
	//	Если все получилось, возыращаем результат
	if ($parseResult) return $parseResult;
	
	//	Увы, действительно страницы не  найдено
	event('site.noPageFound', $requestURL);
	module('message:url:error', "Page not found '$requestURL'");

	return NULL;
}
function renderURLbase($requestURL)
{
	//	Поищем обработчик URL
	$parseRules	= getCacheValue('localURLparse');
	foreach($parseRules as $parseRule => $parseModule)
	{
		if (!preg_match("#^/$parseRule\.htm$#i", $requestURL, $parseResult)) continue;
		//	Если найден, то выполняем
		ob_start();
		module($parseModule, $parseResult);
		$parseResult = ob_get_clean();
		//	Если все получилось, возыращаем результат
		if ($parseResult) return $parseResult;
	}
	return NULL;
}

///	Выполнить функцию по заданному названию, при необходимости подгрузить из файла
function module($fn, $data = NULL){
	@list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn("module_$fn");
	return $fn?$fn($value, $data):NULL;
}
function m($fn, $data = NULL){
	ob_start();
	module($fn, &$data);
	return ob_get_clean();
}

//	Получить указатель на функцию, при необходимости подгрзить файл
function getFn($fnName)
{
	if (function_exists($fnName)) return $fnName;
	
	$templates = getCacheValue('templates');
	$template= $templates[$fnName];
	if (!$template) return NULL;
	
	include_once($template);
	if (function_exists($fnName)) return $fnName;
	
	module('message:fn:error', "Function not found '$fnName'");
	return NULL;
}

//	Прлучить запрашиваемый URL
function getRequestURL()
{
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
	
	$bbCacheExists = true;
	$ini = getGlobalCacheValue('ini');
	if (!is_array($ini))
	{
		$ini = readIniFile(configName);
		setGlobalCacheValue('ini', $ini);
		$bbCacheExists = false;
	}

	//	Найти физический путь корня сайта
	@$globalRootURL	= $ini['globalRootURL'];
	if (!$globalRootURL){
		$globalRootURL	= $_SERVER['REQUEST_URI'];
		$nPos			= strrpos($globalRootURL, '/');
		$globalRootURL	= substr($globalRootURL, 0, $nPos);
	}
	//	like /dev
	define('globalRootURL',	$globalRootURL);
	//	like /www/dev
	define('globalRootPath',dirname(__FILE__));
	
	if (!file_exists('.htaccess'))
		htaccessMake();
}

//	Задать локальные конфигурационные данные для сесстии
function localInitialize()
{
	global $_CACHE_NEED_SAVE, $_CACHE;
	//////////////////////
	define('localHost',		getSiteURL());
	define('localHostPath',	getSitePath(localHost));
	
	//	Загрузить локальный кеш
	define('localCacheFolder', '_cache/'.localHost);
	$_CACHE_NEED_SAVE	= false;
	$_CACHE				= readData(localCacheFolder.'/cache.txt');
	if (!$_CACHE) $_CACHE = array();
	
	//////////////////////
	//	Задать локальные конфигурационные данные для сесстии
	define('localCompiledCode', localCacheFolder.'/modules.php');
	$ini	= getCacheValue('ini');
	if (!is_array($ini))
	{
		//	Initialize cache ini files
		$ini 		= readIniFile(localHostPath."/".configName);
		setCacheValue('ini', $ini);
		
		//	Initialize image path
		$localImagePath = $ini[':images'];
		if (!$localImagePath) $localImagePath = localHostPath.'/images';
		setCacheValue('localImagePath', $localImagePath);
		//	Задать путь хранения изображений
		define('images', $localImagePath);
		
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


		modulesConfigure();
		//	При необходимости вывести сообщения от модулей в лог
		ob_start();
		include_once(localCompiledCode);
		module('message:trace:modules', ob_get_clean());
		
		//	Initialize pages and copy desing files
		$localPages = array();
		pagesInitialize(globalRootPath.'/'.modulesBase,		$localPages);
		pagesInitialize(globalRootPath.'/'.templatesBase,	$localPages);
		pagesInitialize(localHostPath,						$localPages);
	
		$bOK&= pageInitializeCopy(localCacheFolder.'/siteFiles', 		$localPages);
		$bOK = pageInitializeCompile(localCacheFolder.'/compiledPages', $localPages);
		if ($bOK){
			setCacheValue('pages', $localPages);
		}else{
			echo 'Error copy design files';
		}
		event('config.end', $ini);
	}else{
		//	Задать путь хранения изображений
		define('images', getCacheValue('localImagePath'));
		
		//	При необходимости вывести сообщения от модулей в лог
		ob_start();
		include_once(localCompiledCode);
		module('message:trace:modules', ob_get_clean());
	}

	@$template = $ini[getRequestURL()]['template'];
	if (!$template) @$template	= $ini[':']['template'];
	if (!$template) $template	= 'default';
	$GLOBALS['_CONFIG']['page']['template']	= "page.$template";
	$GLOBALS['_CONFIG']['page']['renderLayout']= 'body';
}

function modulesConfigure()
{
	//	Initialize modules and templates
	$localModules = array();
	modulesInitialize(globalRootPath.'/'.modulesBase,	$localModules);
	modulesInitialize(globalRootPath.'/'.templatesBase,	$localModules);
	modulesInitialize(localHostPath.'/'.modulesBase,	$localModules);
	modulesInitialize(localHostPath.'/'.templatesBase,	$localModules);

	$maxModifyTime = 0;
	foreach($localModules as $modulePath){
		$maxModifyTime = max($maxModifyTime, filemtime($modulePath));
	}
	if ($maxModifyTime > @filemtime(localCompiledCode)){
		//	Загрузить все оставшиеся модули
		ob_start();
		foreach($localModules as $name => $modulePath){
			echo "<? // Module $name loaded from  $modulePath ?>\r\n";
			readfile($modulePath);
			echo "\r\n";
		};
		$bOK = file_put_contents_safe(localCompiledCode, ob_get_clean());
		$bOK&= pageInitializeCopy(localCacheFolder.'/siteFiles', $localModules);
		if (!bOK){
			echo 'Error write compiled modules';
			die;
		};
	}
	setCacheValue('modules', $localModules);
}
//	Поиск всех загружаемых модуле  и конфигурационных програм
function modulesInitialize($modulesPath, &$localModules)
{
	//	Поиск конфигурационных файлов
	$configFiles	= getFiles($modulesPath, '^config\..*php$');
	foreach($configFiles as $configFile)
	{
		include_once($configFile);
	}
	//	Поиск модулей
	$files			= getFiles($modulesPath, '^module_.*php$');
	foreach($files as $name => $path)
	{
			// remove ext
		$name = preg_replace('#\.[^.]*$#',		'', $name);
		$localModules[$name] = $path;
	}
	
	$dirs = getDirs($modulesPath, '^_');
	foreach($dirs as $modulePath){
		//	Сканировать поддиректории
		modulesInitialize($modulePath, $localModules);
	};
}

//	Поиск всех страниц и шаблонов
function pagesInitialize($pagesPath, &$pages)
{
	//	Поиск страниц сайта
	$files	= getFiles($pagesPath, '^(page\.|phone\.page\.|tablet\.page\.|template\.)');
	foreach($files as $name => $path){
		$name = preg_replace('#\.[^.]*$#', '', $name);
		$pages[$name] = $path;
	}

	$dirs = getDirs($pagesPath, '^_');
	foreach($dirs as $pagePath){
		//	Сканировать поддиректории
		pagesInitialize($pagePath, $pages);
	};
}

//	Копирование всех дизайнерских файлов из модуля в основной каталог сайта, за исключением системных файлов
function pageInitializeCopy($rootFolder, $pages)
{
	$bOK = makeDir($rootFolder);
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
			if (filemtime($sourcePath) == @filemtime($destPath)) continue;

			if (!@copy($sourcePath, $destPath)) $bOK = false;
			@touch ($destPath, filemtime($sourcePath));
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
function pageInitializeCompile($compilePath, &$pages)
{
	$templates	= array();
	foreach($pages as $name => &$pagePath)
	{
		$compiledPagePath	= "$compilePath/$name.php";
		if (filemtime($pagePath) != @filemtime($compiledPagePath))
		{
			$compiledPage		= file_get_contents($pagePath);
			event('page.compile', &$compiledPage);
			
			if (!$compiledPage) continue;
			if (!file_put_contents_safe($compiledPagePath, $compiledPage)) return false;
			touch($compiledPagePath, filemtime($pagePath));
			
		}
		$pagePath = $compiledPagePath;
		
		if (preg_match('#^template\.#', $name)){
			$name				= preg_replace('#^template\.#', '', $name);
			$templates[$name]	= $pagePath;
		}
	}
	setCacheValue('templates', $templates);
	return true;
}

//	Получить локальный путь к папке с файлами сайта
function getSitePath($siteURL)
{
	$sites		= getGlobalCacheValue('HostSites');
	if (!is_array($sites)){
		$sites = getDirs('_sites');
		if (!$sites) $sires = array();
		setGlobalCacheValue('HostSites', $sites);
	}
	
	if (isset($sites[$siteURL])) return $sites[$siteURL];
	return "_sites/$siteURL";
}

//	Получить адрес текущего сайта
function getSiteURL(){
	$siteURL	= $_SERVER['HTTP_HOST'];
	$siteURL	= preg_replace('#^www\.#', '', $siteURL);
	
	$ini		= getGlobalCacheValue('ini');
	$sites		= $ini[':globalSiteRedirect'];
	if (!is_array($sites)) return $siteURL;

	foreach($sites as $rule => $host){
		if (preg_match("#$rule#i", $siteURL)) return $host;
	}
	return $siteURL;
}

// прочитать INI из файла
function readIniFile($file)
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

//	Записать INI а файл
function writeIniFile($file, &$ini){
	$out = '';
	foreach ($ini as $name => $v){
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
function writeData($path, &$data){
	return file_put_contents_safe($path, serialize($data));
}
function readData($path){
	return @unserialize(file_get_contents($path));
}
//	Глобальный кеш
function globalCacheExists(){
	$ini		= getGlobalCacheValue('ini');
	@$bNoCache	= $ini[':']['useCache'];
	return $bNoCache == 1;
}

function setGlobalCacheValue($name, &$value){
	$GLOBALS['_GLOBAL_CACHE_NEED_SAVE']	= true;
	$GLOBALS['_GLOBAL_CACHE'][$name]	= $value;
}
function getGlobalCacheValue($name){
	return @$GLOBALS['_GLOBAL_CACHE'][$name];
}
function testGlobalCacheValue($name){
	return isset($GLOBALS['_GLOBAL_CACHE'][$name]);
}

//	Локальный кеш
function localCacheExists()
{
	if (defined('localCacheExists')) return localCacheExists;
	
	$ini		= getCacheValue('ini');
	@$bNoCache	= $ini[':']['useCache'];
	define('localCacheExists', $bNoCache == 1);
	return localCacheExists;
}

function setCacheValue($name, &$value){
	$GLOBALS['_CACHE_NEED_SAVE']= true;
	$GLOBALS['_CACHE'][$name]	= $value;
}
function getCacheValue($name){
	return @$GLOBALS['_CACHE'][$name];
}
function testCacheValue($name){
	return isset($GLOBALS['_CACHE'][$name]);
}

//	Выгрузить кеш, если в нем были изменения
function flushCache()
{
	global $_CACHE_NEED_SAVE, $_CACHE;
	
	if (defined('clearCache'))
		clearCache(true);

	if ($_CACHE_NEED_SAVE && localCacheExists()){
		if (!writeData(localCacheFolder.'/cache.txt', $_CACHE)){
			echo 'Error write cache';
		};
	}
	
	global $_GLOBAL_CACHE_NEED_SAVE, $_GLOBAL_CACHE;
	if ($_GLOBAL_CACHE_NEED_SAVE && globalCacheExists()){
		if (!writeData(globalCacheFolder.'/globalCache.txt', $_GLOBAL_CACHE)){
			echo 'Error write global cache';
		};
	}
}

function clearCache($bClearNow = false)
{
	if($bClearNow){
		global $_CACHE_NEED_SAVE, $_CACHE;
		$_CACHE				= array();
		$_CACHE_NEED_SAVE	= false;
		@unlink(localCacheFolder.'/cache.txt');
	}
	
	if (defined('clearCache')) return;
	define('clearCache', true);

	htaccessMake();
	
	module('message', 'Кеш очищен, перезагрузите страницу.');
	module('message:trace', 'Кеш очищен');
}

//	Дублировать объект
function cloneObject(&$db){
   $serialized_contents = serialize($db);
   $res = unserialize($serialized_contents);
   @$res->dbLink = $db->dbLink;
   return $res;
}

//	read		=> link like page356 (internal custom resource indentificator)
//	write		=> link like page356 (internal custom resource indentificator)
//	fileRead	=> link to base folder or file
//	fileWrite	=> link to base folder or file
//	add:baseResource:newResource	=> link
function access($val, $data)
{
	if (!defined('user')) return false;
	
	$bOK = false;
	$parseRules	= getCacheValue('localAccessParse');
	foreach($parseRules as $parseRule => $parseModule)
	{
		if (preg_match("#$parseRule#", $data, $v)){
			if (!module("$parseModule:$val", &$v)) return false;
			$bOK = true;
		}
	}
	return $bOK;
}

function beginAdmin(){
	ob_start();
}
function endAdmin($menu, $bTop = true){
	$content = ob_get_clean();
	if (!$menu) return print($content);
	$menu[':useTopMenu']= $bTop;
	$menu[':layout'] 	= $content;
	module('admin:edit', $menu);
}
function htaccessMake()
{
	$globalRootURL	= globalRootURL;
	@$ctx			= file_get_contents('.htaccess');
	$ctx			= preg_replace("/# <= [^>]*# => [^\s]+\s*/s", '', $ctx);
	
	$ctx	= preg_replace("/[\r\n]+/", "\r\n", $ctx);
	$ctx	= preg_replace("/# <= index.*# => index/s", '', $ctx);
	$ctx	.="\r\n".
	"# <= index\r\n".
	"RewriteEngine On\r\n".
	"RewriteRule (.+)\.htm$	$globalRootURL/index.php\r\n".
	"# => index\r\n";
	
	$ini	= getGlobalCacheValue('ini');
	$sites	= $ini[':globalSiteRedirect'];
	if (!$sites) $sites = array();
	foreach($sites as $rule => $host){
		htaccessMakeHost($rule, $host, &$ctx);
	}
	
	return file_put_contents_safe('.htaccess', $ctx);
}
function htaccessMakeHost($hostRule, $hostName, &$ctx)
{
	//	Initialize image path
	$ini 			= readIniFile("_sites/$hostName/".configName);
	$localImagePath = $ini[':images'];
	if (!$localImagePath) $localImagePath = 'images';
	$localImagePath = trim($localImagePath, '/');

	$safeName	= md5($hostName);
	$ctx	= preg_replace("/# <= $safeName.*# => $safeName/s", '', $ctx);
	
	$globalRootURL = globalRootURL;
	
	$ctx	.= "\r\n".
	"# <= $safeName\r\n".

	"RewriteCond %{HTTP_HOST} $hostRule\r\n".
	"RewriteCond %{REQUEST_FILENAME} !/_sites/\r\n".
	"RewriteCond %{REQUEST_FILENAME} /$localImagePath\r\n".
	"RewriteRule ^($localImagePath/.+)	$globalRootURL/_sites/$hostName/$1\r\n".

	"RewriteCond %{HTTP_HOST} $hostRule\r\n".
	"RewriteCond %{REQUEST_FILENAME} !php$\r\n".
	"RewriteCond %{REQUEST_FILENAME} !/_editor/\r\n".
	"RewriteCond %{REQUEST_FILENAME} !/_cache/\r\n".
	"RewriteCond %{REQUEST_FILENAME} !/_sites/\r\n".
	"RewriteRule (.+)	$globalRootURL/_cache/$hostName/siteFiles/$1\r\n".
	"# => $safeName\r\n";
}
?>
