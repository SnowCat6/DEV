<?
function gallery_file($val, &$data)
{
	$source			= $data['src'];
	$uploadFolder	= $data['upload'];
	if (!$uploadFolder && count($source) < 2){
		if (is_array($source)){
			list(, $uploadFolder) = each($source);
		}else $uploadFolder = $source;
	}
	$f	= getFiles($source);
	galleryUpload($data, 'Нажмите для загрузки файлов, или перетащите файлы сюда');
	if (!$f) return;
?>
<link rel="stylesheet" type="text/css" href="gallery.css"/>
<div class="fileHolder">
<h3>Скачать файлы:</h3>
<? foreach($f as $name => $path){
	$size	= round(filesize($path) / 1000, 2);
	$path	= imagePath2local($path);
	$ext	= explode('.', $name);
	$ext	= end($ext);
?>
<div class="fileIcon {$ext}"><a href="{$path}" target="_blank"><b>{$name}</b> {$size}Кб.</a></div>
<? } ?>
</div>
<? } ?>