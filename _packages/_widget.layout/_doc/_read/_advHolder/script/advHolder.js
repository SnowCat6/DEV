// JavaScript Document

var advSliderDelay = 6000;
var advSliderTimer = 0;

$(function()
{
	$(".advSliderEx")
	.css({position: "relative"})
	.hover(function(){
		clearTimeout(advSliderTimer);
		advSliderTimer = 0;
	}, function(){
		if ($(this).hasClass("lockSlide")) return;
		advSliderTimer = setTimeout('advSliderNext('+advSliderDelay+')', advSliderDelay);
	})
	.each(function()
	{
		var thisElm = $(this);
		var h = $(this).height();
		var w = $(this).find(".advSlider").width();
		$(this).height(h).width(w).attr("imageIndex", 0);
		
		var elm = $(this).find(".advSlider");
		var count = elm.length;
		if (count > 1){
			var html = '';
			for(ix=0; ix<count; ++ ix){
				if (ix) html += '<span></span>';
				cls = $(elm.get(ix)).hasClass('disabled')?' class="disabled"':'';
				html += '<a href="#" imageIndex="'+ix+'"' + cls + '></a>';
			};
			
			var seek = $('<div class="advSeekEx">' + html + '</div>')
			.css("z-index", 5)
			.appendTo($(this));
			seek.css("margin-left", -seek.width());
			
			$(this).find(".advSeekEx a")
			.hover(function(){
				var index= $(this).attr("imageIndex");
				advSliderSet(thisElm, index, 250);
			}).click(function(){
				clearTimeout(advSliderTimer);
				advSliderTimer = 0;

				thisElm.toggleClass("lockSlide");
				
				return false;
			});
			advSliderSet(thisElm, 0);
		}
	});
	
	$(".advSliderEx .advSlider")
	.css({position: "absolute"});
	
	advSliderTimer = setTimeout('advSliderNext('+advSliderDelay+')', advSliderDelay);
});
function advSliderNext(delay)
{
	clearTimeout(advSliderTimer);
	advSliderTimer = 0;
	
	$(".advSliderEx").each(function()
	{
		var index = parseInt($(this).attr("imageIndex"));
		
		var images	= $(this).find('.advSlider');
		var now = $(images.get(index));
		
		oldIndex = index;
		do{
			index = (index + 1) % images.length
			var next = $(images.get(index));
		}while(index != oldIndex && next.hasClass("disabled"));

		advSliderSet($(this), index, 1500);
	});
	advSliderTimer = setTimeout('advSliderNext('+advSliderDelay+')', advSliderDelay);
}

function advSliderSet(thisElm, index, delay)
{
	var oldIndex = thisElm.attr("imageIndex");
	if (index != oldIndex)
	{
		thisElm.attr("imageIndex", index);
	
		var images	= thisElm.find('.advSlider');
		var now = $(images.get(oldIndex));
		var next = $(images.get(index));
		
		next.css('z-index',2).show();
		now.css('z-index',3)
			.fadeOut(delay,function(){
				now.hide();
			});
	}

	$(thisElm.find(".advSeekEx a")
		.removeClass("active").get(index))
		.addClass("active");
}
