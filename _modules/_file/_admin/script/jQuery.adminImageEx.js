// JavaScript Document

$(function()
{
	$(document).on("jqReady ready", function()
	{

		$(".adminImageUploadEx")
		.fileUpload(fnImageFileUpload)
		.each(function()
		{
			$(this).closest(".adminEditArea")
				.find(".adminImage")
				.attr("rel", $(this).attr("rel"))
				.fileUpload("d&d", fnImageFileUpload);
		});

		$(".adminImageDeleteEx")
		.click(function()
		{
			var img = $(this).closest(".adminEditArea").find(".adminImage img");
			if (img.length == 0) return false;

			img.fileDelete(img.attr("src"), function(){
				$(this).remove();
			});
			return false;
		});
	});
});

function fnImageFileUpload(ev)
{
	var holder = $(this).closest(".adminEditArea");
	var image = holder.find(".adminImage");

	for(name in ev){
		var path = ev[name]['path'];
		var size = ev[name]['dimension'].split(' x ');
		
		var w = size[0];
		var h = size[1];
		image.html('<img width="'+w+'px" height="' + h + 'px" src="'+ path + '" />');
		image.parent().css({
			width: w,
			height: h
		});
		break;
	}
}
