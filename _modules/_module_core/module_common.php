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

function beginCache($name)
{
	$cache		= getCacheValue('cache');
	@$thisCache	= $cache[$name];
	if (isset($thisCache)){
		showDocument($thisCache);
		return false;
	}
	ob_start();
	return true;
}

function endCache($name)
{
	$val			= ob_get_clean();
	showDocument($val);
	if (!localCacheExists()) return;
	
	$cache			= getCacheValue('cache');
	$cache[$name]	= $val;
	setCacheValue('cache', $cache);
	module('message:trace', "text cached $name");
}

function setCache($name, $value = NULL)
{
	$cache			= getCacheValue('cache');
	$cache[$name]	= $value;
	if ($value === NULL) unset($cache[$name]);
	setCacheValue('cache', $cache);
}
?>