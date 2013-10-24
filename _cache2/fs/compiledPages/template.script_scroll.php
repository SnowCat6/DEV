<? function script_scroll($val){?><? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
$(function(){
	$(".scroll").css({"height":$(".scroll table").height(), "overflow":"hidden"})
	.mousemove(function(e)
	{
		//	over
		var cut = 80;
		var thisWidth = $(this).width();
		var width = $(this).find("table").width();
		if (width < thisWidth) return;
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
