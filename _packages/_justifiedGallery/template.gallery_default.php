<?
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

	m('script:justifedGallery');
	m('script:lightbox');
?>
<link rel="stylesheet" type="text/css" href="../../_modules/_gallery/css/gallery.css">
<div class="jGallery gallery flat">
<? foreach($files as $path =>$v){ ?>
<a href="{$v[path]} "rel="lightbox{$id}">
{{file:image=src:$path;mask:$mask;size:$size;property.title:$v[name];}}
</a>
<? } ?>
</div>
<? } ?>


<? function script_justifedGallery(&$val)
{
	m('script:jq');
?>
<script src="script/jquery.justifiedGallery.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/justifiedGallery.min.css">
<script>
$(function()
{
	$(document).on("ready jqReady", function()
	{
		$(".jGallery")
		.removeClass('flat')
		.justifiedGallery({
			margins: 4, rowHeight: 200,
			sizeRangeSuffixes: {
				'lt100':'',
				'lt240':'',
				'lt320':'',
				'lt500':'',
				'lt640':'',
				'lt1024':''
			}		
		});
	});
});
</script>
<? } ?>
