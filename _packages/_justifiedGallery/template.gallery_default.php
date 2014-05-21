<?
function gallery_default_before($val, &$data)
{
	module('script:jq');
	m('script:justifedGallery');
	module('page:style', 'gallery.css');
	module('script:lightbox');
}
function gallery_default($val, &$data)
{
	$source	= $data['src'];
	$f		= getFiles($source);

	//	Отсортировать по соотношению сторон	
	$f2	= array();
	foreach($f as $name => $path){
		list($w, $h)		= getimagesize($path);
		if ($w){
			$f2[100*$h/$w][]= $path;
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
	$row	= 0;
	$cols	= $data['cols']?$data['cols']:4;
	if (count($files) < $cols) $cols = count($files);
	$percent= ' width="' . floor(100/$cols) . '%"';
	
	for($ix = 0; $ix < count($files); ++$row){
		for($iix = 0; $iix < $cols; ++$iix){
			$path			= '';
			@list(,$path)	= each($files); ++$ix;
			$table[$row][]	= $path;
		}
	}
	$class = ' id="first"';

	//	Получить параметры
	$mask	= $data['mask'];
	$size	= $data['size'];
	if (!$size) $size = array(150, 150);
	
	$id	= $data['id'];
	if ($id) $id = "[$id]";
	
	galleryUpload($data);
	if (!$table) return;
?>
<link rel="stylesheet" type="text/css" href="gallery.css"/>
<div class="jGallery">
<table border="0" cellspacing="0" cellpadding="0" class="gallery" align="center">
<? foreach($table as $row){ ?>
<tr {!$class}>
<? $class2 = ' id="first"';
foreach($row as $path)
{
	$localPath	= imagePath2local($path);
	$menu		= imageAdminMenu($path);
	
	$comment = $c = file_get_contents("$path.shtml");
	if ($comment) $comment = "<div class=\"comment\">$comment</div>";
	
	$name = $n	= file_get_contents("$path.name.shtml");
	if ($name) $name = "<div class=\"name\">$name</div>";
	
	$ctx	= $name || $comment?"<div class=\"holder\">$name$comment</div>":'';
	if (!$n) $n = $c;
	$n		= strip_tags($n);
?>
    <td {!$class2}{!$percent}>
<? if ($path){ ?>
    <a href="{$localPath}" rel="lightbox{$id}"><? $mask?displayThumbImageMask($path, $mask, '', $n):displayThumbImage($path, $size, '', $n)?></a>
<? } ?>
    </td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; $percent = ''; } ?>
</table>
</div>
<? } ?>
<? function script_justifedGallery(&$val){
	m('scriptLoad',	'script/justifedGallery/js/jquery.justifiedGallery.min.js');
	m('styleLoad',	'script/justifedGallery/css/justifiedGallery.min.css');
?>
<script>
$(function(){
	var html = '';
	$(".jGallery td").each(function(){
		html += $(this).html();
	});
	$(".jGallery").html(html).justifiedGallery({
		margins: 4,
		rowHeight: 200,
		sizeRangeSuffixes: {'lt100':'',  'lt240':'',  'lt320':'',  'lt500':'',  'lt640':'', 'lt1024':''}		
	});
});
</script>
<? } ?>
