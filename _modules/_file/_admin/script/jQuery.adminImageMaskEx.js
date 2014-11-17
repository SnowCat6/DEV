// JavaScript Document

$(function(){
	$(".adminImageMaskHandleEx").click(function()
	{
		var thisElm = $(this);
		var holder = thisElm.parents(".adminEditArea");
		var image = holder.find(".adminMaskImage img");
		if (image.length == 0) return false;
		
		if (holder.hasClass("adminImageActive"))
		{
			holder.removeClass("adminImageActive");
			
			$(this).text($(this).attr("oldEditLabel"));
			$(this).attr("oldEditLabel", '');
			image.draggable("destroy");
		}else{
			holder.addClass("adminImageActive");
			$(this).attr("oldEditLabel", $(this).text());
			$(this).text("Завершить кадрирование");
			
			var maxTop = image.height() - image.parent().height();
			if (image.position().top < -maxTop) image.css("top", 0);
			
			image.draggable({
				axis: "y",
				drag: function(event, ui){
					if (ui.position.top < -maxTop) ui.position.top = -maxTop;
					if (ui.position.top > 0) ui.position.top = 0;
					return true;
				},
				stop:	function(event, ui)
				{
					var top = parseInt(image.css("top"));
					var url = thisElm.attr("href") + "&top=" + top;
		
					$.ajax(url).fail(function(){
						alert("Error");
					});
				}
			});
		}
		return false;
	});
	$(".adminImageMaskUploadEx")
	.fileUpload(fnMaskFileUpload)
	.each(function(){
		$(this).parents(".adminEditArea")
			.find(".adminImage")
			.attr("rel", $(this).attr("rel"))
			.fileUpload("d&d", fnMaskFileUpload);
	});
});
function fnMaskFileUpload(ev)
{
	var image = $(this).parents(".adminEditArea").find(".adminMaskImage");
	
	for(name in ev){
		var path = ev[name]['path'];
		image.html('<img width="100%" src="'+path+'" />');
		break;
	}
}
