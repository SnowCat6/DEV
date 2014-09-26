// JavaScript Document

//	var advAdmin;
var advTimer = null;
var advIndex = -1;
var advCount = 0;

$(function(){
	advCount = $(".advBackground .content").length;
	if (advCount > 1){
		for(ix = 0; ix < advCount; ++ix){
			$(".advSeek").append((ix?'<span></span>':'')+"<div rel='" + ix + "'>" + (ix+1) + advAdmin + "</div>");
		}
	}
if (advAdmin){
		$(".advSeek div div a").each(function(ix, em){
			var ix = parseInt($(this).parent().parent().text()) - 1;
			var e = $($(".advBackground .content").get(ix));
			$(this).attr('href', 'advBanner.htm?edit=' + e.attr("rel"));
		});
		$(document).trigger("jqReady");
}
	$(".advSeek > div").hover(function()
	{
		setAdvIndex($(this).attr("rel"));
		clearTimeout(advTimer);
		advTimer = null;
	}, function(){
		advTimer = setTimeout(setAdvNext, 5000);
	});
	$(".advBackground .content").hover(function(){
		clearTimeout(advTimer);
		advTimer = null;
	}, function(){
		advTimer = setTimeout(setAdvNext, 5000);
	});
	setAdvIndex(0);
	advTimer = setTimeout(setAdvNext, 5000);
});
function setAdvNext(){
	setAdvIndex(advIndex + 1, true);
	clearTimeout(advTimer);
	advTimer = setTimeout(setAdvNext, 5000);
}
function setAdvIndex(ix, useAnimate)
{
	ix = ix % advCount;
	if (isNaN(ix)) ix = 0;
	if (advIndex == ix) return;
	advIndex = ix;
	
	var iNow = $($(".advBackground .content").get(ix));

	if (useAnimate)
	{
		$(".advBackground .content.current")
			.css({"z-index": -1, "opacity": 1})
			.animate({"opacity": 0}, 'slow');
			
		iNow
			.addClass("current")
			.css({"z-index": 0, "opacity": 0})
			.animate({"opacity": 1}, 'slow', function(){
				$(".advBackground .content.current").removeClass("current");
				$(this).addClass("current");
			});
	}else{
		$($(".advBackground .content").removeClass("current"));
		iNow
			.addClass("current")
			.css({"z-index": 0, "opacity": 1})
	}
	
	$($(".advSeek > div")
		.removeClass("current").get(ix))
		.addClass("current");
}
