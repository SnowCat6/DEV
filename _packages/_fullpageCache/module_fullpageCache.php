<?
//	+function module_fullPageCache
function module_fullPageCache($val, &$ev)
{
	if (userID()) return;
	if (!localCacheExists()) return;

	$thisPage		= $ev['url'];
	$renderedPage	= &$ev['content'];

	$ini		= getCacheValue('ini');
	$prefix		= devicePrefix();
	
	switch($ini[':fullpageCache'][$thisPage])
	{
	case 'full':
	//	Перед кешированием проверить наличие параметров
		if ($_POST || $_GET) return;
		$cachePageName = "fullPageCache:$prefix$thisPage";
		break;
	//	Кешировать всегда, для статических страниц
	case 'noCheck':
		$_POST	= array();
		$_GET	= array();
		$cachePageName = "fullPageCache:$prefix$thisPage";
		break;
	default:
		return;
	}

	$pageFileCache	= md5($cachePageName);
	$cachePath		= cacheRoot.'/fullPageCache';
	$ctx 			= memGet($cachePageName);
	if (!$ctx)$ctx	= file_get_contents("$cachePath/$pageFileCache.html");
	if ($ctx) return $renderedPage = $ctx;

	//	Вывести страницу с текущем URL
	renderPage($thisPage, $renderedPage);
	if (is_null($renderedPage)) $renderedPage = '';

	//	Записать полнокешированную страницу
	if (defined('noPageCache') || getNoCache()) return;
	
	memSet($pageCacheName, $renderedPage);
	file_put_contents_safe("$cachePath/$pageFileCache.html", $renderedPage);
}
//	+function module_fullPageCacheClear
function module_fullPageCacheClear($val, $data)
{
	$cachePath		= cacheRoot.'/fullPageCache';
	delTree($cachePath);
}
?>