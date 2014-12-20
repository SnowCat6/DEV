<?
function module_links($fn, &$url)
{
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	global $_CONFIG;
	if (!is_array($_CONFIG['links'])) links_load($db);
	
	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, $url):NULL;
}
function links_load(&$db)
{
	global $_CONFIG;

	$links	= getCache('links', 'ini');
	if (!is_array($links)) reloadLinks($db);
	else{
		//	Преобразование ссылок типа /pagexxx.htm в ЧПУ
		$_CONFIG['links']		= $links;
		//	Для преобрразования ЧПУ в ссылки типа /pagexxx.htm
		$_CONFIG['nativeLink']	= getCache('nativeLink','ini');
	}
}
function links_getLinkBase(&$db, $val, $url)
{
	global $_CONFIG;
	$nativeLink	= &$_CONFIG['nativeLink'];
	$u			= strtolower($url);
	return $nativeLink[$u];
}
function links_url(&$db, $val, $ev)
{
	$url		= $ev['url'];
	$nativeURL	= links_getLinkBase($db, $val, $url);
	if ($nativeURL) renderURLbase($nativeURL, $ev['content']);
}
function links_prepareURL(&$db, $val, &$url)
{
	global $_CONFIG;
	$links	= &$_CONFIG['links'];
	@$u		= $links[$url];
	if ($u) $url = $u;
}
function reloadLinks(&$db)
{
	$links		= array();
	$nativeLink	= array();
	$db->open();
	while($data = $db->next()){
		if (!isset($links[$data['nativeURL']])) $links[$data['nativeURL']]	= $data['link'];
		if (!isset($nativeLink[$data['link']])) $nativeLink[$data['link']]	= $data['nativeURL'];
	}
	setCache('links', 		$links,		'ini');
	setCache('nativeLink',	$nativeLink,'ini');

	global $_CONFIG;
	$_CONFIG['links']		= $links;
	$_CONFIG['nativeLink']= $nativeLink;
}

function links_add(&$db, $val, $url)
{
	$url= preg_replace('#^.*://#',	'', $url);
	$a	= preg_quote("-._~/[]()", '#');
	$url= preg_replace("#[^a-zA-Z\d$a]#",	'',	$url);
	if (!$url) return;

	$url = strtolower(trim($url, '/'));
	if ($url) $url = "/$url";
	else $url = '/';

	$db->deleteByKey('link', $url);
	
	$d = array();
	$d['link']		= $url;
	$d['nativeURL']	= $val;
	$d['user_id']	= userID();
	$iid =  $db->update($d);

	$a	= NULL;
	setCache('links', 		$a, 'ini');
	setCache('nativeLink',	$a, 'ini');
	return $iid;
}

function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);
	reloadLinks($db);
}

function links_get(&$db, $val)
{
	$res = array();
	$val= dbEncString($db, $val);
	$db->open("nativeURL = $val");
	while($data = $db->next()){
		$res[$data['link']] = $data['link'];
	}
	return $res;
}
?>

