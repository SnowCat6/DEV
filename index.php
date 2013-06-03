<?
header('Content-Type: text/html; charset=utf-8');
//	apd_set_pprof_trace();
//	Засечем время начала работы
define('sessionTimeStart', getmicrotime());
define('sessionID',userIP().':'.sessionTimeStart);

define('modulesBase',	'_modules');
define('templatesBase',	'_templates');
define('configName',	'_modules/config.ini');

//	Если запущен на старой версии PHP то определим недостающую функцию
if (!function_exists('file_put_contents')){
	function file_put_contents($name, &$data){
		@$f = fopen($name, 'w'); @$bOK = fwrite($f,$data);	@fclose($f);
		return $bOK;
	}
}

global $_CONFIG;
$_CONFIG = array();
ob_start();
//////////////////////
//	Инициализация данных, глобальный и локальный кеш, задание констант
globalInitialize();
localInitialize();
//////////////////////
//	MAIN CODE
//////////////////////
//	Запуск сайта, обработка модулей вроде аудентификации пользователя
event('site.start', $_CONFIG);
//	Вывести страницу с текущем URL
renderPage(getRequestURL(), $_CONFIG);
//	Получить буффер вывода для обработки
$renderedPage = ob_get_clean();
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
	setCacheValue('ini', $data);

	return true;
}

//	set multiply values int local site config file
function setGlobalIniValues($data)
{
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
	foreach($path as $name){
		$dir .= "$name/";
		if (is_dir($dir)) continue;
		@mkdir($dir);
		@chmod($dir, 0775);
	}
}

//	Применить к файлу права доступа, на некоторых хостингах иначе все работает плохо
function fileMode($path)
{
	if (!is_file($path)) return;
	chmod($path, 0666);
}

// записать гарантированно в файл, в случае неудачи старый файл остается
function file_put_contents_safe($file, &$value)
{
	makeDir(dirname($file));
	return file_put_contents($file, $value, LOCK_EX) != false;
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
function copyFolder($src, $dst, $excludeFilter = '', $bFastCopy = false)
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
			if ($bFastCopy && is_dir($dest)) continue;
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
	@list($fn, $value) = explode(':', $fn, 2);
	$fn = getFn("module_$fn");
	return $fn?$fn($value, &$data):NULL;
}
//	Тоже самое что и module но возвращает выводимое значение
function m($fn, $data = NULL){
	ob_start();
	module($fn, &$data);
	return ob_get_clean();
}

//	вызвать событие для всех обработчиков
function event($eventName, &$eventData)
{
	$event	= getCacheValue('localEvent');
	@$ev	= &$event[$eventName];
	if (!$ev) return;
	
	foreach($ev as &$module){
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

//	Standart snippets
function addSnippet($snippetName, $value){
	$localSnippets = getCacheValue('localSnippets');
	$localSnippets[$snippetName]	= $value;
	setCacheValue('localSnippets', $localSnippets);
}

///	Обработать страницу по заданному URL и вывести в стандартный вывод
function renderPage($requestURL, &$config)
{
	event('site.renderStart', &$config);
	$renderedPage	= renderURL($requestURL);
	$template		= $config['page']['template'];

	//	Загрузка страницы
	$pages		= getCacheValue('pages');
	if (isset($pages[$template])){
		$config['page']['layout'][@$config['page']['renderLayout']] = $renderedPage;
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
//	Найти обработчик URL и вернуть страницу
function renderURLbase($requestURL)
{
	//	Поищем обработчик URL
	$parseRules	= getCacheValue('localURLparse');
	foreach($parseRules as $parseRule => &$parseModule)
	{
		if (!preg_match("#^/$parseRule\.htm$#iu", $requestURL, $parseResult)) continue;
		//	Если найден, то выполняем
		$pageRender = m($parseModule, &$parseResult);
		//	Если все получилось, возыращаем результат
		if ($pageRender) return $pageRender;
	}
	return NULL;
}

//	Получить указатель на функцию, при необходимости подгрзить файл
function getFn($fnName)
{
	if (function_exists($fnName)) return $fnName;
	
	$templates	= getCacheValue('templates');
	$template	= $templates[$fnName];
	if (!$template) return NULL;

	include_once($template);
	m("message:trace", "Included $template file");
	if (function_exists($fnName)) return $fnName;
	
	module('message:fn:error', "Function not found '$fnName'");
	return NULL;
}

//	Прлучить запрашиваемый URL
function getRequestURL()
{
	@$url	= $_GET['URL'];
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
		$nPos			= strpos($globalRootURL, '?');
		if ($nPos) $globalRootURL = substr($globalRootURL, 0, $nPos);
		$nPos			= strrpos($globalRootURL, '/');
		$globalRootURL	= substr($globalRootURL, 0, $nPos);
	}
	//	like /dev
	define('globalRootURL',	$globalRootURL);
	//	like /www/dev
	define('globalRootPath',dirname(__FILE__));
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

		$gini	= getGlobalCacheValue('ini');
		$host	= getSiteURL();
		@$enable= $gini[":enable:$host"];

		modulesConfigure($enable);
		//	При необходимости вывести сообщения от модулей в лог
		ob_start();
		include_once(localCompiledCode);
		module('message:trace:modules', ob_get_clean());
		m("message:trace", "Included ".localCompiledCode." file");
		
		//	Initialize pages and copy desing files
		$localPages = array();
		pagesInitialize(globalRootPath.'/'.modulesBase,		$localPages, $enable);
		pagesInitialize(globalRootPath.'/'.templatesBase,	$localPages, $enable);
		pagesInitialize(localHostPath,						$localPages, $enable);
	
		$bOK = pageInitializeCopy(localCacheFolder.'/siteFiles', 		$localPages);
		$bOK&= pageInitializeCompile(localCacheFolder.'/compiledPages', $localPages);
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
		m("message:trace", "Included ".localCompiledCode." file");
	}

	@$template = $ini[getRequestURL()]['template'];
	if (!$template) @$template	= $ini[':']['template'];
	if (!$template) $template	= 'default';
	$GLOBALS['_CONFIG']['page']['template']		= "page.$template";
	$GLOBALS['_CONFIG']['page']['renderLayout']	= 'body';

	if (!file_exists('.htaccess'))
		htaccessMake();
}

function modulesConfigure(&$enable)
{
	//	Initialize modules and templates
	$localModules = array();
	modulesInitialize(globalRootPath.'/'.modulesBase,	$localModules, $enable);
	modulesInitialize(globalRootPath.'/'.templatesBase,	$localModules, $enable);
	modulesInitialize(localHostPath.'/'.modulesBase,	$localModules, $enable);
	modulesInitialize(localHostPath.'/'.templatesBase,	$localModules, $enable);

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
function modulesInitialize($modulesPath, &$localModules, &$enable)
{
	$module = basename($modulesPath);
	if (isset($enable[$module])) return;
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
			if (filemtime($sourcePath) == @filemtime($destPath)) continue;

			if (!@copy($sourcePath, $destPath)){
				$bOK = false;
				continue;
			}
			@touch($destPath, filemtime($sourcePath));
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
	$templates			= array();
	$comiledTemplates	= array();
	$compiledFileName	= "$compilePath/compiled.php3";
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

		$compiledPagePath	= "$compilePath/$fileName";
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
	
	if ($comiledFileTime > @filemtime($compiledFileName))
	{
		$compiledTemplate	= '';
		foreach($comiledTemplates as $name => &$pagePath)
		{
			$compiledPage		= file_get_contents($pagePath);
			event('page.compile', &$compiledPage);
			$compiledTemplate	.= "<? //	Template $name loaded from  $pagePath ?>\r\n";
			$compiledTemplate	.=$compiledPage;
		}
		file_put_contents_safe($compiledFileName, $compiledTemplate);
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
function writeIniFile($file, &$ini)
{
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
	if($bClearNow)
	{
		global $_CACHE_NEED_SAVE, $_CACHE;
		$_CACHE				= array();
		$_CACHE_NEED_SAVE	= false;
		//	Временное наименование кеша для удаления
		$tmpCache = localCacheFolder.'.tmp';
		//	Удалить предыдущий кеш, если раньше не удалось
		delTree($tmpCache);
		//	Переименовать кеш, моментальное удаление
		if (@rename(localCacheFolder, $tmpCache)){
			//	Если переименование удалось, то удалить временный кеш
			delTree($tmpCache);
		}else{
			//	Если переименование не удалось, попробовать удалить что есть
//			set_time_limit(200);
//			delTree(localCacheFolder);
		}
		return;
	}
	
	if (defined('clearCache')) return;
	define('clearCache', true);

	htaccessMake();
	
	module('message', 'Кеш очищен, перезагрузите страницу.');
	module('message:trace', 'Кеш очищен');
}

//	read		=> link like page356 (internal custom resource indentificator)
//	write		=> link like page356 (internal custom resource indentificator)
//	fileRead	=> link to base folder or file
//	fileWrite	=> link to base folder or file
//	add:baseResource:newResource	=> link
function access($val, $data)
{
	$bOK = false;
	$parseRules	= getCacheValue('localAccessParse');
	foreach($parseRules as $parseRule => $parseModule)
	{
		if (preg_match("#^$parseRule$#", $data, $v)){
			if (!module("$parseModule:$val", &$v)) return false;
			$bOK = true;
		}
	}
	return $bOK;
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
	"AddDefaultCharset UTF-8\r\n\r\n".
	"RewriteEngine On\r\n".
	"RewriteRule (.+)\.htm$	$globalRootURL/index.php\r\n".
	"# => index\r\n";
	
	$ini	= getGlobalCacheValue('ini');
	$sites	= $ini[':globalSiteRedirect'];
	if (is_array($sites))
	{
		foreach($sites as $rule => $host){
			htaccessMakeHost($rule, $host, &$ctx);
		}
	}else{
		$sites		= getGlobalCacheValue('HostSites');
		if (count($sites) != 1){
			foreach($sites as $host){
				$host = substr($host, strlen('_sites/'));
				htaccessMakeHost(preg_quote($host), $host, &$ctx);
			}
		}else{
			list($ix, $host) = each($sites);
			$host = substr($host, strlen('_sites/'));
			htaccessMakeHost(".*", $host, &$ctx);
		}
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

	$safeName	= md5($hostRule);
	$ctx		= preg_replace("/# <= $safeName.*# => $safeName/s", '', $ctx);
	
	$globalRootURL = globalRootURL;
	
	$ctx	.= "\r\n".
	"# <= $safeName\r\n".

	"RewriteCond %{HTTP_HOST} $hostRule\r\n".
	"RewriteCond %{REQUEST_FILENAME} /$localImagePath\r\n".
	"RewriteRule ^($localImagePath/.+)	$globalRootURL/_sites/$hostName/$1\r\n".

	"RewriteCond %{HTTP_HOST} $hostRule\r\n".
	"RewriteCond %{REQUEST_FILENAME} !/_|php$\r\n".
	"RewriteRule (.+)	$globalRootURL/_cache/$hostName/siteFiles/$1\r\n".
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

?>
