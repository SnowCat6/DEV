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
/*<![CDATA[*/
$(function(){
	$(".gallery.small").mousemove(function(e){
		//	over
		var cut = 50;
		var thisWidth = $(this).width();
		var width = $(this).find("table").width();
		var widthDiff = width - thisWidth;

		var percent = (e.pageX - ($(this).offset().left + cut))/(thisWidth - cut*2);
		if (percent < 0) percent = 0;
		if (percent > 1) percent = 1;

		$(this).find("table").css("left", -Math.round(percent*widthDiff));
	});
});
/*]]>*/
</script>
<? } ?>