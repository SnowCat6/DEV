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
	galleryUpload($data, 'Нажмите для загрузки файлов');
	if (!$f) return;
?>
<link rel="stylesheet" type="text/css" href="gallery.css"/>
<div class="fileHolder">
<h3>Скачать файлы:</h3>
<? foreach($f as $name => $path)
{
	$ext	= explode('.', $name);
	$ext	= end($ext);
	//	Если это ссылка на файл, то считать положение файла
	if ($ext == 'link')
	{
		$path2	= file_get_contents($path);
		if ($path2){
			$path	= localRootPath . '/' . $path2;
			$name	= basename($path);
			$ext	= explode('.', $name);
			$ext	= end($ext);
		}
	}
	
	$size	= round(filesize($path) / 1000, 2);
	$path	= imagePath2local($path);
?>
<div class="fileIcon {$ext}" title="{$name}"><a href="{$path}" target="_blank"><span><b>{$name}</b> {$size}Кб.</span></a></div>
<? } ?>
</div>
<? } ?>