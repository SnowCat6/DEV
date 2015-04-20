// JavaScript Document

$(function()
{
	$(".adminImageSizeUploadEx")
	.fileUpload(fnSizeFileUpload)
	.each(function(){
		var e = $(this).parents(".adminEditArea")
			.find(".adminImageSize")
			.attr("rel", $(this).attr("rel"))
			.fileUpload("d&d", fnSizeFileUpload);
	});
});

function fnSizeFileUpload(ev)
{
	var holder = $(this).closest(".adminEditArea");

	var image = holder
		.find(".adminImageSize");

	for(name in ev){
		var path = ev[name]['path'];
		var size = ev[name]['dimension'].split(' x ');
		
		var w = parseInt(image.css('max-width'));
		var h = parseInt(image.css('max-height'));
		if (h){
			var r1 = w / h;
			var r2 = size[0] / size[1];
			if (r1 < r2)	image.html('<img width="'+w+'px" src="'+ path + '" />');
			else	image.html('<img height="'+h+'px" src="'+ path + '" />');
		}else{
			image.html('<img width="'+w+'px" src="'+ path + '" />');
		}
		break;
	}
}
