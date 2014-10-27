// JavaScript Document

$(function()
{
	$(document).on("ready jqReady", contentFXready);
});
function contentFXready()
{
	$(".contentFXpaging a").not(".adminEditMenu a")
		.unbind("click.contentFX")
		.on("click.contentFX", contentFXpaging);
		
	$(".contentFX.image")
		.wrapInner('<div class="content"></div>');
		
	$(window)
		.scroll(contentFXfnAutoscroll)
		.resize(contentFXfnAutoscroll);
		
	contentFXfnAutoscroll();
}
function contentFXfnAutoscroll()
{
	$(".contentFX.image").each(function()
	{
		var pageHeight = $(document).height();
		var windowTop = $(window).scrollTop();
		var windowHeight = $(window).height();
		
		var elmTop = $(this).position().top;
		var elmHeight = $(this).height();
		
		var percentTop = (elmTop - windowTop) / windowHeight;
		var percentBottom = (elmTop + elmHeight - windowTop) / windowHeight;
		
		percentTop = Math.min(1, percentTop);
		percentTop = Math.max(0, percentTop);

		percentBottom = Math.min(1, percentBottom);
		percentBottom = Math.max(0, percentBottom);
		
		var percent = (percentTop + percentBottom)/2;
//			console.log(percent);
		
		var elmInner = $(this).find("> div");
		var topOffset = Math.round((elmInner.height()) * percent);

		topOffset = Math.max(0, topOffset);
		topOffset = Math.min(elmInner.height() - elmHeight, topOffset);
		
		elmInner.css({top: -topOffset});
	});
};

function contentFXpaging()
{
	var thisElm = $(this);
	var url = thisElm.attr("href");
	
	var ctx = $(".contentFXcontent");
	if (ctx.length == 0) return;
	
	$('#contentFXwrapper').unbind().remove();
	
	$("<div id='contentFXwrapper' />")
	.load(url, function()
	{
		
		var oldWith = ctx.width();
		var oldHeight = ctx.height();
		
		var ctxNew = $(this)
		.find(".contentFXcontent")
		.width(oldWith);
		
		var newHeight = ctxNew.height();
		
		var holder = $("#contentFXwrapperHolder");
		if (holder.length == 0)
		{
			ctx.wrap('<div id="contentFXwrapperHolder" />');
			var holder = $("#contentFXwrapperHolder")
			.css({
				position: "relative",
				overflow: "hidden"
			})
		};
		holder
			.height(oldHeight).width(oldWith)
			.append(ctxNew)
			.find("> .contentFXcontent")
			.css({
				position: "absolute",
				left: 0, top: 0
				});

		var newHeight = ctxNew.height();
		holder.animate({
			height: newHeight
			});
		
		ctx.css({
			"z-index": 1
		})
		.fadeOut(function(){
			$(this).remove();
		});
		
		ctxNew
		.css({
			left: "10%",
			"z-index": 2,
			"opacity": 0
			})
		.animate({
			left: 0,
			opacity: 1
			}, function(){
			ctxNew.removeAttr("style");
		});
		
		$(document).trigger("jqReady");
	});
	return false;
}