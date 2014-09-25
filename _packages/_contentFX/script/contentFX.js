// JavaScript Document

$(function(){
	$(".contentFX.image").wrapInner('<div class="content"></div>');
	$(window).scroll(function(){
		$(".contentFX.image").each(function()
		{
			var pageHeight = $(document).height();
			var windowTop = $(window).scrollTop();
			var windowHeight = $(window).height();
			
			var elmTop = $(this).offset().top;
			var elmHeight = $(this).height();
			
			var percentTop = (elmTop - windowTop) / windowHeight;
			var percentBottom = (elmTop + elmHeight - windowTop) / windowHeight;
			
			percentTop = Math.min(1, percentTop);
			percentTop = Math.max(0, percentTop);

			percentBottom = Math.min(1, percentBottom);
			percentBottom = Math.max(0, percentBottom);
			
			var percent = 1-(percentTop + percentBottom)/2;
			console.log(percent);
			
			var elmInner = $(this).find("> div");
			var topOffset = Math.round((elmInner.height() - elmHeight) * percent);
			
			topOffset = Math.max(0, topOffset);
			topOffset = Math.min(elmInner.height() - elmHeight, topOffset);
			
			elmInner.css({top: -topOffset});
		});
	});
});