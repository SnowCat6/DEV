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
	$url = strtolower($url);
	makeSQLValue($url);
	$db->open("link = $url");
	$data = $db->next();
	if (!$data) return;
	
	$nativeURL = $data['nativeURL'];
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
		$url = $data['link'];
		$links[$url] = $data['link'];
	}else{
		$links[$url] = '';
	}
	
	setCacheValue('links', $links);
}
?>