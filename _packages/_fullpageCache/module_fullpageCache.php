<? function module_fullpageCache(&$val, &$cachePageName)
{
	if (userID() || $_POST || $_GET) return;
	
	$thisPage	= getURL('#');
	$ini		= getCacheValue('ini');
	$pageName	= $ini[':fullpageCache'][$thisPage];
	if ($pageName == 'full'){
		$prefix	= '';
		if (isPhone())	$prefix = 'phone';
		if (isTablet())	$prefix = 'tablet';
		$cachePageName = "fullPageCache$prefix:$thisPage";
	}
}
?>