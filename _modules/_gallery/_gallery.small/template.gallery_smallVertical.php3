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
?>
<link rel="stylesheet" type="text/css" href="gallerySmall.css">
<div class="vertical gallery small">
<table cellpadding="0" cellspacing="0">
<? foreach($files as $path){
$path2	= imagePath2local($path);
?>
<tr><td><a href="{$path2}" rel="lightbox{$id}"{!$title}><? displayThumbImage($path, array(50, 50))?></a></td></tr>
<? } ?>
</table>
</div>
<? } ?>