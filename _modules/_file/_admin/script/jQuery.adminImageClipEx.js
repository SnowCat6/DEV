// JavaScript Document

$(function()
{
	$(document)
	.click(function(){
		$(".adminImageActive").each(function(){
			fnClipStopClip($(this));
		});
	})
	.on("jqReady ready", function()
	{
		$(".adminImageClipHandleEx").click(function()
		{
			var holder = $(this).closest(".adminEditArea");
			
			if (holder.hasClass("adminImageActive"))
				fnClipStopClip(holder);
			else fnClipStartClip(holder);
	
			return false;
		});
		
		$(".adminImageClipUploadEx")
		.fileUpload(fnClipFileUpload)
		.each(function()
		{
			$(this).closest(".adminEditArea")
				.find(".adminImageClip")
				.attr("rel", $(this).attr("rel"))
				.fileUpload("d&d", fnClipFileUpload);
		});

		$(".adminImageClipDeleteEx")
		.click(function()
		{
			var img = $(this).closest(".adminEditArea").find(".adminImageClip  img");
			if (img.length == 0) return false;

			img.fileDelete(img.attr("src"), function(){
				$(this).remove();
			});
			return false;
		});
	
	});
});
function fnClipStartClip(holder)
{
	if (holder.hasClass("adminImageActive")) return;
	
	holder.css("z-index", 100);
	var menuElm	= holder.find(".adminImageClipHandleEx");

	var image = holder.find(".adminImageClip img");
	if (image.length == 0) return false;

	holder.addClass("adminImageActive");
	menuElm.attr("oldEditLabel", menuElm.text());
	menuElm.text("Завершить");
	
	var maxTop = image.height() - image.parent().height();
	if (image.position().top < -maxTop){
		image.css("top", 0);
		fnClipSave(holder);
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
			fnClipSave(holder);
		}
	});
}
function fnClipSave(holder)
{
	var menuElm	= holder.find(".adminImageClipHandleEx");
	var image = holder.find(".adminImageClip img");
	
	var top = parseInt(image.css("top"));
	var url = menuElm.attr("href") + "&top=" + top;

	$.ajax(url).fail(function(){
		alert("Error");
	});
}
function fnClipStopClip(holder)
{
	if (!holder.hasClass("adminImageActive")) return;
	holder.removeClass("adminImageActive");

	holder.css("z-index", 0);
	var menuElm	= holder.find(".adminImageClipHandleEx");
	
	menuElm.text(menuElm.attr("oldEditLabel"));
	menuElm.attr("oldEditLabel", '');

	var image = holder.find(".adminImageClip img");
	image.draggable("destroy");
}
function fnClipFileUpload(ev)
{
	var holder = $(this).closest(".adminEditArea");
	fnClipStopClip(holder);

	var image = holder.find(".adminImageClip");
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
