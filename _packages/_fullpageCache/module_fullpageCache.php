<? function module_fullpageCache(&$val, &$cachePageName)
{
	if (userID()) return;
	
	$thisPage	= getURL('#');
	$ini		= getCacheValue('ini');
	
	if (isPhone())	$prefix = 'Phone';
	else
	if (isTablet())	$prefix = 'Tablet';
	
	switch($ini[':fullpageCache'][$thisPage]){
	case 'full':
	//	Перед кешированием проверить наличие параметров
		if ($_POST || $_GET) return;
		$cachePageName = "fullPageCache$prefix:$thisPage";
		break;
	//	Кешировать всегда, для статических страниц
	case 'noCheck':
		$cachePageName = "fullPageCache$prefix:$thisPage";
		break;
	}
}
?>