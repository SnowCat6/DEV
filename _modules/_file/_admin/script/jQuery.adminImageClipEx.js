// JavaScript Document

$(function(){
	$(".adminImageClipHandleEx").click(function()
	{
		var holder = $(this).parents(".adminEditArea");
		
		if (holder.hasClass("adminImageActive"))
			fnClipStopClip(holder);
		else fnClipStartClip(holder);

		return false;
	});
	
	$(".adminImageClipUploadEx")
	.fileUpload(fnClipFileUpload)
	.each(function()
	{
		$(this).parents(".adminEditArea")
			.find(".adminImageClip")
			.attr("rel", $(this).attr("rel"))
			.fileUpload("d&d", fnClipFileUpload);
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
	if (image.position().top < -maxTop) image.css("top", 0);
	
	image.draggable(
	{
		axis: "y",
		drag: function(event, ui){
			if (ui.position.top < -maxTop) ui.position.top = -maxTop;
			if (ui.position.top > 0) ui.position.top = 0;
			return true;
		},
		stop:	function(event, ui)
		{
			var top = parseInt(image.css("top"));
			var url = menuElm.attr("href") + "&top=" + top;

			$.ajax(url).fail(function(){
				alert("Error");
			});
		}
	});
}
function fnClipStopClip(holder)
{
	if (!holder.hasClass("adminImageActive")) return;

	holder.css("z-index", 0);
	var menuElm	= holder.find(".adminImageClipHandleEx");

	holder.removeClass("adminImageActive");
	
	menuElm.text(menuElm.attr("oldEditLabel"));
	menuElm.attr("oldEditLabel", '');

	var image = holder.find(".adminImageClip img");
	image.draggable("destroy");
}
function fnClipFileUpload(ev)
{
	var holder = $(this).parents(".adminEditArea");
	fnClipStopClip(holder);

	var image = holder.find(".adminImageClip");
	
	for(name in ev){
		var path = ev[name]['path'];
		var size = ev[name]['dimension'].split(' x ');
		image.html('<img width="100%" src="'+path+'" />');
		break;
	}
}
