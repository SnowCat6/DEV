<?
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
		list($w, $h) = getimagesize($path);
		$f2[100*$h/$w][]	= $path;
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
<? $class2 = ' id="first"'; foreach($row as $path){?>
    <td {!$class2}><a href="{$path}" rel="lightbox{$id}"><? $mask?displayThumbImageMask($path, $mask):displayThumbImage($path, $size)?></a></td>
<? $class2 = NULL; } ?>
</tr>
<? $class = NULL; } ?>
</table>
<? } ?>