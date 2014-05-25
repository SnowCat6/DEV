<? function module_fullpageCache(&$val, &$cachePageName)
{
	if (userID()) return;
	
	$thisPage	= getURL('#');
	$ini		= getCacheValue('ini');
	$pageName	= $ini[':fullpageCache'][$thisPage];
	
	$prefix	= '';
	if (isPhone())	$prefix = 'phone';
	if (isTablet())	$prefix = 'tablet';
	//	Перед кешированием проверить наличие параметров
	if ($pageName == 'full'){
		if ($_POST || $_GET) return;
		$cachePageName = "fullPageCache$prefix:$thisPage";
	}
	//	Кешировать всегда, для статических страниц
	if ($pageName == 'noCheck'){
		$cachePageName = "fullPageCache$prefix:$thisPage";
	}
}
?>