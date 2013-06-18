<?
function gallery_smallVertical($val, $data)
{
	$files = getFiles($data['src']);
	if (!$files) return;

	module('script:scroll');
?>
<link rel="stylesheet" type="text/css" href="gallerySmall.css">
<div class="vertical gallery small">
<table cellpadding="0" cellspacing="0">
<? foreach($files as $path){ ?>
<tr><td><? displayThumbImage($path, array(50, 50), '', '', $path)?></td></tr>
<? } ?>
</table>
</div>
<? } ?>