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
	$id	= (int)$val;
	if (!$id) $id = (int)$data;
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
		if (!is_array($data)) $data		= array();
		$d2			= array();
		$d2['id']	= $id;
		$d2['src']	= $db->folder($id).'/Gallery/';
		$d2['upload']	= $d2['src'];
		if ($data['cols']) $d2['cols']		= $data['cols'];
		if ($data['mask']) $d2['mask']		= $data['mask'];
		event('gallery.config', $d2);
		module('gallery:default', $d2);
		if (getNoCache() == $noCache) endCompile($d);
		else cancelCompile($d);
	}
}
function imageAdminMenu($path)
{
	if (!canEditFile($path)) return;
	$menu	= array();
	$menu['Описание#ajax_edit']	= getURL("file_images_comment/$path");
	$menu['Удалить']			= getURL("file_images_delete/$path", 'delete');
	m('script:file_delete');
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
function gallery_fileUpload($val, $data){
	return galleryUpload($data);
}
function galleryUpload($data, $message = '')
{
	$source			= $data['src'];
	$uploadFolder	= $data['upload'];
	if (!$uploadFolder && count($source) < 2){
		if (is_array($source)){
			list(, $uploadFolder) = each($source);
		}else $uploadFolder = $source;
	}

	if (!canEditFile($uploadFolder)) return;

	setNoCache();
	m('script:fileUpload');
	$uploadFolder	= imagePath2local($uploadFolder);
	$id				= md5($uploadFolder);
	
	m('page:style', 'gallery.css');
	if (!$message) $message = 'Нажмите сюда, чтобы загрузить файлы в фотогалерею, или перетащите для загрузки';
?>
<div class="galleryUpload" id="file<?= $id?>"><?= $message?></div>
<script>
$(function(){
	$(".galleryUpload#file<?= $id?>")
		.fileUpload('<?= htmlspecialchars($uploadFolder)?>', function(){
		document.location.reload();
	});
});
</script>
<? return true; } ?>
