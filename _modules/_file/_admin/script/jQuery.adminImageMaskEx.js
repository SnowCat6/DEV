// JavaScript Document

$(function()
{
	$(document)
	.click(function(){
		$(".adminImageMaskActive").each(function(){
			fnMaskStopClip($(this));
		});
	})
	.on("jqReady ready", function()
	{
		$(".adminImageMaskHandleEx").click(function()
		{
			var holder = $(this).closest(".adminEditArea");
			
			if (holder.hasClass("adminImageMaskActive"))
				fnMaskStopClip(holder);
			else fnMaskStartClip(holder);
	
			return false;
		});
		
		$(".adminImageMaskUploadEx")
		.fileUpload(fnMaskFileUpload)
		.each(function()
		{
			$(this).closest(".adminEditArea")
				.find(".adminMaskImage")
				.attr("rel", $(this).attr("rel"))
				.fileUpload("d&d", fnMaskFileUpload);
		});

		$(".adminImageMaskDeleteEx")
		.click(function()
		{
			var img = $(this).closest(".adminEditArea").find(".adminMaskImage img");
			if (img.length == 0) return false;

			img.fileDelete(img.attr("src"), function(){
				$(this).remove();
			});
			return false;
		});
	});
});
function fnMaskStartClip(holder)
{
	if (holder.hasClass("adminImageMaskActive")) return;
	
	var menuElm	= holder.find(".adminImageMaskHandleEx");

	var image = holder.find(".adminMaskImage img");
	if (image.length == 0) return false;

	holder.addClass("adminImageMaskActive");
	menuElm.attr("oldEditLabel", menuElm.text());
	menuElm.text("Завершить");
	
	var maxTop = image.height() - image.parent().height();
	if (image.position().top < -maxTop){
		image.css("top", 0);
		fnMaskSave(holder);
	}
	
	image.draggable(
	{
		axis: "y",
		drag: function(event, ui){
			if (ui.position.top < -maxTop) ui.position.top = -maxTop;
			if (ui.position.top > 0) ui.position.top = 0;
			return true;
		},
		stop:	function(event, ui){
			fnMaskSave(holder);
		}
	});
}
function fnMaskSave(holder)
{
	var image = holder.find(".adminMaskImage img");
	var menuElm	= holder.find(".adminImageMaskHandleEx");
	
	var top = parseInt(image.css("top"));
	var url = menuElm.attr("href") + "&top=" + top;

	$.ajax(url).fail(function(){
		alert("Error");
	});
}
function fnMaskStopClip(holder)
{
	if (!holder.hasClass("adminImageMaskActive")) return;
	holder.removeClass("adminImageMaskActive");

	var menuElm	= holder.find(".adminImageMaskHandleEx");

	menuElm.text(menuElm.attr("oldEditLabel"));
	menuElm.attr("oldEditLabel", '');

	var image = holder.find(".adminMaskImage img");
	image.draggable("destroy");
}
function fnMaskFileUpload(ev)
{
	var holder = $(this).closest(".adminEditArea");

	fnMaskStopClip(holder);

	var image = holder.find(".adminMaskImage");
	var rImg = image.width() / image.height();
	
	for(name in ev){
		var path = ev[name]['path'];
		var size = ev[name]['dimension'].split(' x ');
		if (rImg > size[0] / size[1]) 
		image.html('<img width="100%" src="'+path+'" />');
		else	image.html('<img height="100%" src="'+path+'" />');

		break;
	}
}
