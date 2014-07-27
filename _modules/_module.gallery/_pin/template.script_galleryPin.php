<? function script_galleryPin(&$val){
	m('script:jq_ui');
	m('script:fileUpload');
?>
<script>
$(function(){
	$("a[href*=gallery_pin]").click(function()
	{
		var image = $($(this).parents().parent().find(".adminImage .adminImageImage"));
		
		if ($(this).attr("oldEditLabel"))
		{
			$(this).parent().parent().removeClass("adminImageActive");
			$(this).html($(this).attr("oldEditLabel"));
			$(this).attr("oldEditLabel", '');
			image.draggable("destroy");
			var top = parseInt(image.css("top"));
			var url = $(this).attr("href") + "&top=" + top;
			console.log("AJAX: " + url);
			$.ajax(url).fail(function(){
				alert("Error");
			});
		}else{
			$(this).parent().parent().addClass("adminImageActive");
			$(this).attr("oldEditLabel", $(this).html());
			$(this).text("Сохранить");
			var maxTop = image.height() - image.parent().height();
			image.draggable({
				axis: "y",
				drag: function(event, ui){
					if (ui.position.top > 0) ui.position.top = 0;
					if (ui.position.top < -maxTop) ui.position.top = -maxTop;
					return true;
				}
				});
		}
		return false;
	});
	$(".adminImageUpload").fileUpload(function(ev){
		document.location = document.location;
	});
});
</script>
<? } ?>
<? function style_galleryPin(&$val){ ?>
<style>
.adminImage{
	overflow:hidden;
	position:relative;
}
.adminImage .adminImageImage{
	position:relative;
}
.adminImage .adminImageMask{
	position:absolute;
	top:0; left:0;
}
.adminImageActive .adminImageMask{
	visibility:hidden;
}
.adminImage .ui-draggable{
	cursor:move;
}
</style>
<? } ?>