<? function module_fullpageCache(&$val, &$cachePageName)
{
	if (userID()) return;
	
	$thisPage	= getURL('#');
	$ini		= getCacheValue('ini');
	$pageName	= $ini[':fullpageCache'][$thisPage];
	if ($pageName == 'full') $cachePageName = "fullPageCache:$thisPage";
}
?>