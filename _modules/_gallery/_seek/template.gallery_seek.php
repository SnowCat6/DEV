<? function gallery_seek($val, $data)
{
	galleryUpload($data);
	
	$files	= gallery_files($val, $data['src']);
	if (!$files) return;

	$mask	= $data['mask'];
	$size	= $data['size'];
	$url	= $data['url'];
?>
{{script:CrossFade}}
<link rel="stylesheet" type="text/css" href="css/gagllerySeek.css">
<script src="script/gagllerySeek.js"></script>

<div class="gallerySeek">
<?
foreach($files as $path =>$v){
	$menu	= $data['hasAdmin']?imageAdminMenu($path):array();
?>
<div class="gallerySeekImage">
{{file:image=src:$path;mask:$mask;size:$size;adminMenu:$menu;property.title:$v[name];property.href:$url;property.rel:lightbox$id;property.class:$class}}
</div>
<? } ?>
</div>

<? } ?>
