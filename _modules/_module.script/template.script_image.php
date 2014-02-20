<? function script_image($val){ m('script:jq'); ?>
<script>
$(function(){
	$("a[rel*=lightbox]")
	.hover(function(){
		$("#preview").remove();
		$(".preview").css({"position": "relative", "z-index": 999})
			.append("<div id='preview'><div><img src='"+ this.href +"' alt='Image preview' /></div></div>")
			.children()
		.css({
			"position": "absolute",
			"background": "#f8f8f8",
			"z-index": 999, "background": "#f0f0f0",
			"left": 0, "right": 0, "height": 300,
			"display": "none", "overflow": "hidden"
		}).fadeIn("fast");
	}, function(){
		$("#preview").fadeOut("fast", function(){
			$(this).remove();
		});
	}).mousemove(function(ev)
	{
		var src = $($(this).find("img"));
		var parentOffset = $(this).parent().offset();;
		var zoomX	= (ev.pageX - parentOffset.left)/src.width();
		var zoomY	= (ev.pageY - parentOffset.top)/src.height();
		if (zoomY > 1) zoomY = 0;

		var preview = $("#preview");
		var previewImage = $("#preview img");
		
		var x = previewImage.width()>preview.width()?(previewImage.width()-preview.width())*zoomX:0;
		var y = previewImage.height()>preview.height()?(previewImage.height()-preview.height())*zoomY:0;
		
		$("#preview div").css({
			"position": "absolute",
			"left": -x, "top": -y
		});
	});
});
</script>
<? } ?>
