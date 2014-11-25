<?
function gallery_smallVertical($val, $data)
{
	$files = getFiles($data['src']);
	if (!$files) return;

	@$id	= $data['id'];
	if ($id) $id = "[$id]";

	@$title	= htmlspecialchars($data['title']);
	if ($title) $title = "title=\"$title\"";

	module('script:scroll');
	module('script:lightbox');
?>
<link rel="stylesheet" type="text/css" href="css/gallerySmall.css">
<div class="vertical gallery small">
<table cellpadding="0" cellspacing="0">
<? foreach($files as $path){
$path2	= imagePath2local($path);
?>
<tr><td><a href="{$path2}" rel="lightbox{$id}"{!$title}>
	{{image:displayThumbImage=src:$path;size:50x50}}
</a></td></tr>
<? } ?>
</table>
</div>
<? } ?>