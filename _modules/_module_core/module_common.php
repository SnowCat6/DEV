<?
//	Отключить кеширование страниц
function nocache()
{
	if (defined('noCache')) return;
	define('noCache', true);
	
    ini_set('session.cache_limiter', 'nocache'); #добавляем HTTP заголовок Expires
    ini_set('session.cache_expire', 0);          #добавляем HTTP заголовок Cache-Control

    #header('Expires: Thu, 01 Jan 1998 00:00:00 GMT');
    #header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    #динамическая генерация даты, возможно, позволит не "отпугнуть" роботов-индексаторов поисковых систем.
    header('Expires: '       . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', strtotime('-1 day')) . ' GMT');

    # HTTP/1.1
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Cache-Control: max-age=0', false);
    # HTTP/1.0
    header('Pragma: no-cache');
}

function ksortUTF8(&$array){
	$a = array();
	foreach($array as $key => $val){
		$a[iconv('UTF-8', 'windows-1251', $key)] = $val;
	}
	ksort($a);
	$array = array();
	foreach($a as $key => $val){
		$array[iconv('windows-1251', 'UTF-8', $key)] = $val;
	}
}
function beginCache($name){
	$cache		= getCacheValue('cache');
	@$thisCache	= $cache[$name];
	if (isset($thisCache)){
		showDocument($thisCache);
		return false;
	}
	ob_start();
	return true;
}

function endCache($name){
	$cache			= getCacheValue('cache');
	$cache[$name]	= ob_get_clean();
	setCacheValue('cache', $cache);
	showDocument($cache[$name]);
}

function setCache($name, $value = NULL)
{
	$cache			= getCacheValue('cache');
	$cache[$name]	= $value;
	if ($value === NULL) unset($cache[$name]);
	setCacheValue('cache', $cache);
}
?>