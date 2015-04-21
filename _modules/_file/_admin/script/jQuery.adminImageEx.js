// JavaScript Document

$(function()
{
	$(".adminImageUploadEx")
	.fileUpload(fnImageFileUpload)
	.each(function(){
		var e = $(this).closest(".adminEditArea")
			.find(".adminImage")
			.attr("rel", $(this).attr("rel"))
			.fileUpload("d&d", fnImageFileUpload);
	});
});

function fnImageFileUpload(ev)
{
	var holder = $(this).closest(".adminEditArea");
	var image = holder
		.find(".adminImage");

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
