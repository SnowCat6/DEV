<?
function module_gallery($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	if (!$fn) $fn = 'default';

	$fn = getFn("gallery_$fn");
	return $fn?$fn($val, $data):NULL;
}
function gallery_doc(&$val, &$data)
{
	$id	= (int)$data;
	if (!$id) $id = currentPage();
	if (!$id || defined("galleryShowed$id")) return;
	
	define("galleryShowed$id", true);
	module('script:lightbox');
	module('page:style', 'gallery.css');
	
	$db	= module('doc');
	$d	= $db->openID($id);
	$noCache	= getNoCache();
	$cache		= access('write', "doc:$id")?'':"gallery/$val";
	if (beginCompile($d, $cache))
	{
		$d2			= array();
		$d2['src']	= $db->folder($id).'/Gallery';
		event('gallery.config', $d2);
		module('gallery:default', $d2);
		if (getNoCache() == $noCache) endCompile($d);
		else cancelCompile($d);
	}
}
function imageAdminMenu($path){
	if (!canEditFile($path)) return;
	$menu	= array();
	$menu['Комментарий#ajax_edit']	= getURL("file_images_comment/$path");
	return $menu;
}
function imageBeginAdmin($menu){
	if (!$menu) return;
	beginAdmin($menu);
}
function imageEndAdmin($menu){
	if (!$menu) return;
	endAdmin($menu);
}

?>
