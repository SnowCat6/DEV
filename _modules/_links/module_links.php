<?
function module_links($fn, &$url)
{
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, $url):NULL;
}
function links_getLinkBase(&$db, $val, $url)
{
	if (!$url) return;
	
	$links	= config::get(':links');
	if (!is_array($links)) $links = reloadLinks($db);
	
	$url	= rtrim($url, '/');
	$u		= strtolower($url?$url:'/');
	$u		= array_search($u, $links);
	return is_bool($u)?NULL:$u;
}
function links_url(&$db, $val, $ev)
{
	$url		= $ev['url'];
	$nativeURL	= links_getLinkBase($db, $val, $url);
	if ($nativeURL) renderURLbase($nativeURL, $ev['content']);
}
function links_prepareURL(&$db, $val, &$url)
{
	$links	= config::get(':links');
	if (!is_array($links)) $links = reloadLinks($db);

	@$u		= $links[$url];
	if ($u) $url = $u;
}
function reloadLinks(&$db)
{
	$links		= array();
	$db->open();
	while($data = $db->next())
	{
		$native	= $data['nativeURL'];
		if (!isset($links[$native])) $links[$native]	= $data['link'];
	}
	setCache('links', 		$links,		'ini');
	config::set(':links',	$links);
	return $links;
}

function links_get(&$db, $nativeURL)
{
	$res	= array();
	$val	= dbEncString($db, $nativeURL);
	$db->open("nativeURL = $val");
	while($data = $db->next()){
		$res[$data['link']] = $data['link'];
	}
	return $res;
}
?>

