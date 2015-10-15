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
//	+function gallery_find
function gallery_find($val, $search)
{
	$folder	= $search['folder'];
	if ($folder) return new fileSource(getSiteFiles($folder));
}
//	Галерея для документов
//	+function doc_gallery
function doc_gallery($db, $val, $data)
{
	list($iid, $template) = explode(':', $val, 2);
	if ($iid){
		$id	= alias2doc($iid);
	}else{
		$id = alias2doc($data);
		if (!$id) $id = currentPage();
	}
	if (!$id || defined("galleryShowed$id")) return;
	define("galleryShowed$id", true);
	
	if (!is_array($data)) $data		= array();
	
	$d2			= array();
	$d2['id']	= $id;
	$d2['src']	= $data['src']?$data['src']:$db->folder($id).'/Gallery/';
	$d2['upload']	= $data['upload']?$data['upload']:$d2['src'];
	$d2['message']	= $data['message'];
	$d2['property']	= $data['property'];

	if ($data['url'])	$d2['url']		= $data['url'];
	else $d2['url'] = getURL($db->url($id));

	if ($data['cols'])	$d2['cols']		= $data['cols'];
	if ($data['mask'])	$d2['mask']		= $data['mask'];
	event('gallery.config', $d2);
	
	$d			= $db->openID($id);
	$noCache	= getNoCache();
	$cache		= access('write', "doc:$id")?'':"gallery/$val";
	
	$fn			= getFn(array(
		"gallery_$template",
		"gallery_default"
	));
	$fn2		= getFn(array(
		"gallery_$template"."_before",
		"gallery_default_before"
	));
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
function imageBeginAdmin($menu){
	beginAdmin($menu);
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
	$uploadFolder	= imagePath2local($uploadFolder);
	$id				= md5($uploadFolder);

	if (!$message)	$message = $data['message'];
	if (!$message)	$message = 'Нажмите сюда, чтобы загрузить файлы в фотогалерею, или перетащите для загрузки';
	
	m('script:fileUpload');
	$json	= array('uploadFolder' => $uploadFolder);
?>
<link rel="stylesheet" type="text/css" href="css/gallery.css">
<script src="script/gallery.js"></script>

<div class="galleryUpload" rel="{$json|json}">
{!$message}
</div>
<? return true; } ?>
