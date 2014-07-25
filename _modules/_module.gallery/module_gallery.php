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
		if (getNoCache() == $noCache) endCompile();
		else cancelCompile();
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
	m('script:fileUpload');
	$uploadFolder	= imagePath2local($uploadFolder);
	$id				= md5($uploadFolder);
	
	m('page:style', 'gallery.css');
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
<?
//	doc:title:mask	=> path to mask
//	doc:title:		=> width or array(w,h)
//	+function doc_titleImage
function doc_titleImage(&$db, &$mode, &$data)
{
	list($id, $mode) = explode(':', $mode, 2);
	if ($mode){
		$fn	= getFn("doc_titleImage_$mode");
		return $fn?$fn($db, $id, $data):NULL;
	}

	$title	= module("doc:cacheGet:$id:titleImage");
	if (!isset($title)){
		$title = docTitleImage($id);
		m("doc:cacheSet:$id:titleImage", "$title");
	}

	if ($data){
		$w = 0; $h = 0;
		if (is_array($data)){
			$w		= $data[0]; $h = $data[1];
			$name	= $w.'x'.$h;;
		}else{
			$w 		= $data;
			$name	= $w;
		}
		
		$t	= module("doc:cacheGet:$id:titleImage:$name");
		if (isset($t)) return $t;
		
		ob_start();
		$title	= displayThumbImage($title, $data);
		ob_get_clean();
		m("doc:cacheSet:$id:titleImage:$name", $title);
	}
	return $title;
}
function doc_titleImage_mask(&$db, &$id, &$data)
{
	$mask	= $data['mask'];
	if (!$mask) return;

	$bPopup	= $data['popup']?true:false;
	$bPopup	&= $data['popup'] != 'false';
	if ($bPopup) m('script:lightbox');
	$title	= module("doc:cacheGet:$id:titleImageMask:$mask:$bPopup");
	
	if (!isset($title))
	{
		if ($data['title'] != 'false'){
			$d	= $db->openID($id);
			$t	= $d['title'];
		}
		ob_start();
		$image = module("doc:titleImage:$id");
		$title = displayThumbImageMask($image, $mask, '', $t, $bPopup?$image:'');
		if (!$title && $data['noImage']) echo "<img src=\"$data[noImage]\" />";
		$title	= ob_get_clean();
		m("doc:cacheSet:$id:titleImageMask:$mask:$bPopup", $title);
	}
	echo $title;
}
function doc_titleImage_size(&$db, &$id, &$data)
{
	$w = 0; $h = 0;
	if (is_array($data)){
		$w		= $data[0]; $h = $data[1];
		if (count($data) == 1){
			list($w, $h) = explode('x', $w);
		}
	}else{
		list($w, $h) = explode('x', $data);
	}
	if ($h){
		$name	= $w.'x'.$h;;
		$w		= array($w, $h);
	}else{
		$name	= $w;
	}

	$bPopup	= $data['popup']?true:false;
	$bPopup	&= $data['popup'] != 'false';
	if ($bPopup) m('script:lightbox');

	$title	= module("doc:cacheGet:$id:titleImageSize:$name:$bPopup");
	if (!$title){
		$d	= $db->openID($id);
		$t	= $d['title'];
	
		ob_start();
		$t2	= module("doc:titleImage:$id");
		displayThumbImage($t2, $w, '', $t, $bPopup?$t2:NULL);
		$title	= ob_get_clean();
		m("doc:cacheSet:$id:titleImageSize:$name:$bPopup", $title);
	}
	echo $title;
}
?>
