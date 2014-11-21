<?
function module_gallery($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	if (!$fn) $fn = 'default';

	$fn = getFn("gallery_$fn");
	$fn2= getFn("gallery_$fn".'_before');
	if ($fn2) $fn2($data);
	return $fn?$fn($val, $data):NULL;
}
//	Галерея для документов
//	+function doc_gallery
function doc_gallery($db, &$val, &$data)
{
	$id	= (int)$val;
	if (!$id) $id = (int)$data;
	if (!$id) $id = currentPage();

	if (!$id || defined("galleryShowed$id")) return;
	define("galleryShowed$id", true);
	
	if (!is_array($data)) $data		= array();
	
	$d2			= array();
	$d2['id']	= $id;
	$d2['src']	= $db->folder($id).'/Gallery/';
	$d2['upload']	= $d2['src'];
	$d2['message']	= $data['message'];
	if ($data['cols']) $d2['cols']		= $data['cols'];
	if ($data['mask']) $d2['mask']		= $data['mask'];
	event('gallery.config', $d2);
	
	$d			= $db->openID($id);
	$noCache	= getNoCache();
	$cache		= access('write', "doc:$id")?'':"gallery/$val";
	
	$fn			= getFn("gallery_default");
	$fn2		= getFn("gallery_default_before");
	if ($fn2) $fn2($d2);
	
	if ($fn && beginCompile($d, $cache))
	{
		$fn($val, $d2);
		endCompile();
	}
}

//	меню редактирования
function imageAdminMenu($path)
{
	if (!canEditFile($path)) return;
	$menu	= array();
	$menu['Описание#ajax_edit']	= getURL("file_images_comment/$path");
	$menu['Удалить']			= getURL("file_images_delete/$path", 'delete');
	m('script:file_delete');
	return $menu;
}
function imageBeginAdmin($menu, $bTop = true){
	beginAdmin($menu, $bTop);
}
function imageEndAdmin(){
	endAdmin();
}
function gallery_files(&$val, &$source)
{
	//	Отсортировать по соотношению сторон	
	$sz	= array();
	$f2	= array();
	$f	= getFiles($source);
	foreach($f as $name => $path){
		list($w, $h) = getimagesize($path);
		if ($w){
			$f2[round(100*$h/$w)][]= $path;
			$sz[$path]	= array('w'=>$w, 'h'=>$h);
		}else unset($f[$name]);
	}
	ksort($f2);

	//	Создать массив изображений
	$files	= array();
	foreach($f2 as &$val)
	{
		foreach($val as $path)
		{
			$files[$path] = array(
				'path'=>imagePath2local($path),
				'name'=>file_get_contents("$path.name.shtml"),
				'comment'=>file_get_contents("$path.shtml"),
				'size'=>$sz[$path]
				);
		}
	}
	return $files;
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

	m('page:style', 'css/gallery.css');
	if (!$message)	$message = $data['message'];
	if (!$message)	$message = 'Нажмите сюда, чтобы загрузить файлы в фотогалерею, или перетащите для загрузки';
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
