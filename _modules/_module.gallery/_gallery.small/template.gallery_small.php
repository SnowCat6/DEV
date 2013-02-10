<?
function gallery_small($val, $data)
{
	$files = getFiles($data['src']);
	if (!$files) return;
?>
<link rel="stylesheet" type="text/css" href="gallerySmall.css">
<div class="gallery small">
<table cellpadding="0" cellspacing="0"><tr>
<? foreach($files as $path){ ?>
<td><? displayThumbImage($path, array(50, 50), '', '', $path)?></td>
<? } ?>
</tr></table>
</div>
<script language="javascript" type="text/javascript">
$(function(){
	$(".gallery.small").mousemove(function(e){
		//	over
		var thisWidth = $(this).width();
		var width = $(this).find("table").width();
		var widthDiff = width - thisWidth;
		if (widthDiff <= 0) return;
		var needOffset = Math.round((e.pageX - $(this).offset().left)/thisWidth*widthDiff);
		$(this).find("table").css("left", -needOffset);
	});
});
</script>
<? } ?>