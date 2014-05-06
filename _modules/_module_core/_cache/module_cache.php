<?
$GLOBALS['_CONFIG']['cacheStackName']	= array();

//	Установить значение кеша
function setCache($key, &$value)
{
	global $_CACHE, $_CACHE_NEED_SAVE;
	$_CACHE_NEED_SAVE = true;
	
	$cache		= &$_CACHE['cache'];
	$cache[$key]= $value;
	return true;
}
//	Получить значение кеша
function getCache($key)
{
	global $_CACHE;
	$cache	= &$_CACHE['cache'];
	return $cache[$key];
}
//	Очистить ке по по ключевым словам
function clearCache($keyReg)
{
	global $_CACHE, $_CACHE_NEED_SAVE;
	$cache	= &$_CACHE['cache'];
	if (!$keyReg){
		$cache = array();
		$_CACHE_NEED_SAVE = true;
		return;
	}
	
	foreach($cache as $name => &$val)
	{
		if (!preg_match("#^$keyReg#", $name)) continue;
		unset($cache[$name]);
		$_CACHE_NEED_SAVE = true;
	}
}

//	Кеширует поток вывода в кеше, возвращает false если значение найдено и можно пропустить вывод данных
//	if (beginCache($name)){....; endCache(); };
function beginCache($key)
{
	if (!$key){
		pushStackName('cache', $key);
		return true;
	}

	global $_CACHE;
	$cache		= &$_CACHE['cache'];

	$thisCache	= $cache[$key];
	if (isset($thisCache)){
		//	Обработать динамический кешируемый код, и вывести на экран
		showDocument($thisCache);
		return false;
	}
	//	Начать захват потока вывода
	ob_start();
	pushStackName('cache', $key);
	return true;
}
//	Записывает поток вывода в кеш
function endCache()
{
	$key	= popStackName('cache');
	if (!$key) return;
	
	//	Получить поток вывода
	$val	= ob_get_clean();
	//	Обработать динамический кешируемый код, и вывести на экран
	showDocument($val);
	
	global $_CACHE, $_CACHE_NEED_SAVE;
	$_CACHE_NEED_SAVE = true;
	
	$cache			= &$_CACHE['cache'];
	$cache[$key]	= $val;

	module('message:trace', "text cached $name");
}
?>