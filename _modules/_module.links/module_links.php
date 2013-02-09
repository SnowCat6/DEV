<?
function module_links($fn, &$url){
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, &$url):NULL;
}

function links_url(&$db, $val, $url)
{
	$nativeLink	= getCacheValue('nativeLink');
	$u			= strtolower($url);
	@$nativeURL	= &$nativeLink[$u];
	if ($nativeURL){
		echo renderURLbase($nativeURL);
		return;
	}
	
	makeSQLValue($u);
	$db->open("link = $u");
	$data = $db->next();
	if ($data){
		$nativeURL = $data['nativeURL'];
		echo renderURLbase($nativeURL);
	}
	setCacheValue('nativeLink', $nativeLink);
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