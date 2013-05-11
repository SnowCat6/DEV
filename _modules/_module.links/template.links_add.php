<?
function links_add(&$db, $val, $url)
{
	$url = preg_replace('#^(.*+://)#',		'', $url);
	$url = preg_replace('#(\.\w+)$#',		'', $url);
	$url = preg_replace('#([^\d\w_/]+)$#',	'', $url);
	if (!$url) return;
	
	$a = NULL;
	setCacheValue('links', $a);
	setCacheValue('nativeLink', $a);

	$url = strtolower(trim($url, '/'));
	if ($url) $url = "/$url.htm";
	else $url = '/';
	
	$d = array();
	$d['link']		= $url;
	$d['nativeURL']	= $val;
	$d['user_id']	= 0;
	return $db->update($d);
}
?>