<?
function gallery_small($val, $data)
{
	m('script:scroll');
	m('page:style', 'gallerySmall.css');
	@$files = getFiles($data['src']);
	if (!$files) return;
?>
<link rel="stylesheet" type="text/css" href="gallerySmall.css">
<div class="scroll gallery small">
<table cellpadding="0" cellspacing="0"><tr>
<? foreach($files as $path){ ?>
<td><? displayThumbImage($path, array(50, 50), '', '', $path)?></td>
<? } ?>
</tr></table>
</div>
<? } ?>