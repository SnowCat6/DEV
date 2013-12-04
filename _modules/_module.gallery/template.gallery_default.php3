<?
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
function gallery_default($val, &$data)
{
	$f	= getFiles($data['src']);
	if (!$f) return;

	//	Получить параметры
	$mask	= $data['mask'];
	if (!$mask){
		$size	= $data['size'];
		if (!$size) $size = array(150, 150);
	}
	
	$id	= $data['id'];
	if ($id) $id = '[$id]';

	//	Отсортировать по соотношению сторон	
	$f2	= array();
	foreach($f as $name => $path){
		list($w, $h)		= getimagesize($path);
		if ($w){
			$f2[100*$h/$w][]	= $path;
		}else{
			unset($f[$name]);
		}
	}
	ksort($f2);
	
	//	Создать массив изображений
	$files	= array();
	foreach($f2 as &$val){
		foreach($val as $path) $files[] = $path;
	}

	//	Создать табличку
	$row = 0; $cols = 4;
	for($ix = 0; $ix < count($files); ++$row){
		for($iix = 0; $iix < $cols; ++$iix){
			$path			= '';
			@list(,$path)	= each($files); ++$ix;
			$table[$row][]	= $path;
		}
	}
	$class = ' id="first"';
?>
<link rel="stylesheet" type="text/css" href="gallery.css"/>
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr {!$class}>
<? $class2 = ' id="first"'; foreach($row as $path){
	$localPath	= imagePath2local($path);
	$menu		= imageAdminMenu($path);
	$comment	= file_get_contents("$path.html");
?>
    <td {!$class2}>
<? imageBeginAdmin($menu) ?>
    <a href="{$localPath}" rel="lightbox{$id}"><? $mask?displayThumbImageMask($path, $mask):displayThumbImage($path, $size)?></a>
    <div>{!$comment}</div>
<? imageEndAdmin($menu) ?>
    </td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; } ?>
</table>
<? } ?>