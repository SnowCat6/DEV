<?
$links	= getCacheValue('links');
if (!is_array($links)){
	$db			= module('links');
	$links		= array();
	$nativeLink	= getCacheValue('nativeLink');
	$db->open();
	while($data = $db->next()){
		$links[$data['nativeURL']]	= $data['link'];
		$nativeLink[$data['link']]	= $data['nativeURL'];
	}
	setCacheValue('links', 		$links);
	setCacheValue('nativeLink', $nativeLink);
}

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
	return @$nativeLink[$u];
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
	if ($u) $url = $u;
}
?>