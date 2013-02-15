<?
function module_links($fn, &$url){
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, &$url):NULL;
}
function links_getLinkBase(&$db, $val, $url)
{
	$nativeLink	= getCacheValue('nativeLink');
	$u			= strtolower($url);
	@$nativeURL	= &$nativeLink[$u];
	if ($nativeURL)
		return $nativeURL;
		
	makeSQLValue($u);
	$db->open("link = $u");
	$data = $db->next();
	if ($data)
		$nativeURL = $data['nativeURL'];
		
	setCacheValue('nativeLink', $nativeLink);
	return $nativeURL;
}
function links_url(&$db, $val, $url)
{
	$nativeURL	= links_getLinkBase($db, $val, $url);
	if ($nativeURL)
		echo renderURLbase($nativeURL);
}
function links_prepareURL(&$db, $val, &$url)
{
	$links	= getCacheValue('links');
	@$u		= $links[$url];
	if (is_string($u)){
		if ($u) $url = $u;
		return;
	}

	$u		= $url;
	makeSQLValue($u);
	$db->open("nativeURL = $u");
	
	if ($data = $db->next()){
		$links[$url] = $data['link'];
	}else{
		$links[$url] = '';
	}

	setCacheValue('links', $links);
}
?>