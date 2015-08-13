// JavaScript Document
$(function()
{
	$(document).on("ready jqReady", function()
	{
		$(".jGallery")
		.removeClass('flat')
		.justifiedGallery({
			margins: 4, rowHeight: 200,
			sizeRangeSuffixes: {
				'lt100':'',
				'lt240':'',
				'lt320':'',
				'lt500':'',
				'lt640':'',
				'lt1024':''
			}		
		});
	});
});
