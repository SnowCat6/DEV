// JavaScript Document

var previewLoaded = false;
var mouseX = mouseY = 0;
var previewDB = new Array();
$(function(){
	$(document).on("jqReady ready", function()
	{
		holder = $("<div id='previewHolder'></div>")
			.css("z-index", 1999)
			.hide()
			.appendTo('body');

		$(".previewLink a, a.preview")
		.hover(function()
		{
			previewLoaded = true;
			
			var prefix	= 'preview_';
			var cfg = null;
			try{
				cfg = $.parseJSON($(this).attr("rel"));
				if (cfg['preview_prefix']) prefix = cfg['preview_prefix'];
			}catch(e){}

			var lnk = prefix + $(this).attr("href").replace(/^\//, '');
			
			var data = previewDB[lnk];
			if (typeof data == 'string')
			{
				if (!data) return;
				showPreview(data);
				return;
			}
			
			$.ajax(lnk).done(function(data)
			{
				$("#previewHolder").hide();
				previewDB[lnk] = data;
				
				if (!previewLoaded) return;
				if (!data) return;
				showPreview(data);
			});
		}, function (){
			previewLoaded = false;
			$("#previewHolder").hide();
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
function showPreview(data)
{
	$("#previewHolder").html(data).show();
	previewMove();
}
function previewMove()
{
	var o = $('#previewHolder');
	var x = mouseX+15, y = mouseY+15;
	var w = $(window).width() - 25, h = $(window).height() - 25;
	if (x + o.width() > w) x = Math.max(0, w - o.width());
	if (y + o.height()> h) y = Math.max(0, h - o.height());
	o.css({left:x, top:y});
}
