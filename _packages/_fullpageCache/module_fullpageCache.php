<? function module_fullpageCache(&$val, &$ev)
{
	if (userID()) return;

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

	if (defined('memcache')){
		 $ctx 			= memGet($cachePageName);
	}else{
		$pageCacheName	= md5($cachePageName);
		$cachePath		= cacheRoot.'/fullPageCache/';
		$ctx			= file_get_contents("$cachePath$pageCacheName.html");
	}
	if ($ctx) return $renderedPage = $ctx;

	//	Вывести страницу с текущем URL
	renderPage($thisPage, $renderedPage);
	if (is_null($renderedPage)) $renderedPage = '';

	//	Записать полнокешированную страницу
	if (defined('noPageCache') || getNoCache()) return;
	
	if (defined('memcache')){
		memSet($pageCacheName, $renderedPage);
	}else{
		makeDir($cachePath);
		file_put_contents("$cachePath$pageCacheName.html", $renderedPage);
	}
}
?>