<?
function gallery_default($val, &$data)
{
	$source			= $data['src'];
	$uploadFolder	= $data['upload'];
	if (!$uploadFolder && count($source) < 2){
		if (is_array($source)){
			list(, $uploadFolder) = each($source);
		}else $uploadFolder = $source;
	}
	$f	= getFiles($source);

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
	if (count($files) < $cols) $cols = count($files);
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
<? if (canEditFile($uploadFolder)){
	setNoCache();
	m('script:fileUpload');
	$uploadFolder	= imagePath2local($uploadFolder);
?>
<div class="galleryUpload">Нажмите сюда, чтобы загрузить файлы в фотогалерею, или перетащите для загрузки</div>
<script>
$(function(){
	$(".galleryUpload").fileUpload('{$uploadFolder}', function(){
		document.location.reload();
	});
});
</script>
<? } ?>
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr {!$class}>
<? $class2 = ' id="first"'; foreach($row as $path){
	$localPath	= imagePath2local($path);
	$menu		= imageAdminMenu($path);
	$comment	= file_get_contents("$path.shtml");
	if ($comment) $comment = "<div class=\"comment\">$comment</div>";
?>
    <td {!$class2}>
<? imageBeginAdmin($menu) ?>
    <a href="{$localPath}" rel="lightbox{$id}"><? $mask?displayThumbImageMask($path, $mask):displayThumbImage($path, $size)?></a>
    {!$comment}
<? imageEndAdmin($menu) ?>
    </td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; } ?>
</table>
<? } ?>