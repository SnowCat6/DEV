// JavaScript Document

var previewLoaded = false;
var mouseX = mouseY = 0;
var previewDB = new Array();
$(function(){
	$(document).on("jqReady ready", function()
	{
		$(".previewLink a, a.preview")
		.hover(function()
		{
			previewLoaded = true;
			
			var prefix	= 'preview_';
			try{
				cfg = $.parseJSON($(this).attr("rel"));
				if (cfg['preview_prefix']) prefix = cfg['preview_prefix'];
			}catch(e){}
			
			var lnk = prefix + $(this).attr("href").replace(/^\//, '');
			
			var data = previewDB[lnk];
			if (typeof data == 'string')
			{
				if (!data) return;
			
				$("<div id='previewHolder'>")
				.css("z-index", 999)
				.html(data)
				.appendTo('body');
				previewMove();
				return;
			}
			
			$.ajax(lnk).done(function(data)
			{
				$("#previewHolder").remove();
				previewDB[lnk] = data;
				
				if (!previewLoaded) return;
				if (!data) return;

				$("<div id='previewHolder'>")
				.css("z-index", 999)
				.html(data)
				.appendTo('body');
				previewMove();
			});
		}, function (){
			previewLoaded = false;
			$("#previewHolder").remove();
		})
		.removeClass('previewLink')
		.removeClass('preview');
	});
	
	$('body').on("mousemove.preview", function(e)
	{
		mouseX = e.clientX;
		mouseY = e.clientY;
		if (!previewLoaded) return;
		previewMove();
	});
});
function previewMove()
{
	var overlay = $('#previewHolder');
	var x = mouseX+15, y = mouseY+15;
	var w = $(window).width() - 25, h = $(window).height() - 25;
	if (x + overlay.width() > w) x = Math.max(0, w - overlay.width());
	if (y + overlay.height()> h) y = Math.max(0, h - overlay.height());
	overlay.css({left:x, top:y});
}
