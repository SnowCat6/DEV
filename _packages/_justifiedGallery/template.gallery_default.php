<?
// +function gallery_default
function gallery_default($val, &$data)
{
	galleryUpload($data);
	
	$files	= gallery_files($val, $data['src']);
	if (!$files) return;
	
	//	Получить параметры
	$mask	= $data['mask'];
	$size	= $data['size'];
	if (!$size) $size = array(2*150, 2*150);

	$id	= $data['id'];
	if ($id) $id = "[$id]";

	m('script:jq');
	m('script:lightbox');
?>
<link rel="stylesheet" type="text/css" href="../../_modules/_gallery/css/gallery.css">
<script src="script/jquery.justifiedGallery.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/justifiedGallery.min.css">
<script src="script/jquery.jGallery.js"></script>

<div class="jGallery gallery flat">
<? foreach($files as $path =>$v){ ?>
	<module:file:image 
        src="$path"
        mask="$mask"
        size="$size"
        property.title = "$v[name]"
        property.href="$v[path]"
        property.rel="lightbox$id"
    />
<? } ?>
</div>
<? } ?>
