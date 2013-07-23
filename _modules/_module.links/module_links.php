<?
$links	= getCacheValue('links');
if (!is_array($links)) reloadLinks();
else{
	$GLOBALS['_SETTINGS']['links']		= getCacheValue('links');;
	$GLOBALS['_SETTINGS']['nativeLink']	= getCacheValue('nativeLink');;
}

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
	$nativeLink	= &$GLOBALS['_SETTINGS']['nativeLink'];
	$u			= strtolower($url);
	return $nativeLink[$u];
}
function links_url(&$db, $val, $url)
{
	$nativeURL	= links_getLinkBase($db, $val, $url);
	if ($nativeURL) echo renderURLbase($nativeURL);
}
function links_prepareURL(&$db, $val, &$url)
{
	$links	= &$GLOBALS['_SETTINGS']['links'];
	@$u		= $links[$url];
	if ($u) $url = $u;
}
function reloadLinks()
{
	$db			= module('links');
	$links		= array();
	$nativeLink	= array();
	$db->open();
	while($data = $db->next()){
		$links[$data['nativeURL']]	= $data['link'];
		if (!isset($nativeLink[$data['link']])) $nativeLink[$data['link']]	= $data['nativeURL'];
	}
	setCacheValue('links', 		$links);
	setCacheValue('nativeLink', $nativeLink);

	$GLOBALS['_SETTINGS']['links']		= $links;
	$GLOBALS['_SETTINGS']['nativeLink']	= $nativeLink;
}
?>
<?
function links_add(&$db, $val, $url)
{
	$url= preg_replace('#^.*://#',	'', $url);
//	$url= preg_replace('#^.*/#',	'', $url);
//	$url= preg_replace('#\..*#',	'',	$url);
	$a	= preg_quote("-._~/[]()", '#');
	$url= preg_replace("#[^a-zA-Z\d$a]#",	'',	$url);
	if (!$url) return;

	$url = strtolower(trim($url, '/'));
	if ($url) $url = "/$url";
	else $url = '/';
	
	$d = array();
	$d['link']		= $url;
	$d['nativeURL']	= $val;
	$d['user_id']	= 0;
	$iid =  $db->update($d);

	reloadLinks();
	return $iid;
}
?>
<?
function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);
	reloadLinks();
}
?>
<?
function links_get(&$db, $val)
{
	$res = array();
	makeSQLValue($val);
	$db->open("nativeURL = $val");
	while($data = $db->next()){
		$res[$data['link']] = $data['link'];
	}
	return $res;
}
?>

