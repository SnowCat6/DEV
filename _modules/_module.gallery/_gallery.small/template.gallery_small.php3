<?
function gallery_small($val, $data)
{
	m('script:scroll');
	m('page:style', 'gallerySmall.css');
	@$files = getFiles($data['src']);
	if (!$files) return;

	@$id	= $data['id'];
	if ($id) $id = "[$id]";
	
	@$title	= htmlspecialchars($data['title']);
	if ($title) $title = "title=\"$title\"";
?>
<link rel="stylesheet" type="text/css" href="gallerySmall.css">
<div class="scroll gallery small">
<table cellpadding="0" cellspacing="0"><tr>
<? foreach($files as $path){ ?>
<td><a href="{$path}" rel="lightbox{$id}"{!$title}><? displayThumbImage($path, array(50, 50))?></a></td>
<? } ?>
</tr></table>
</div>
<? } ?>