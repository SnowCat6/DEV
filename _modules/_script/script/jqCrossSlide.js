var _CrossSliderSliders = new Array();

(function()
{
	
	$.fn.CrossSlide = function(method, options)
	{
		var methods = {
			init:		thisInit,
			showNext:	thisShowNext,
		};
	
		return methods[method]?
			methods[method].apply(this, Array.prototype.slice.call(arguments, 1)):
			methods['init'].apply(this, Array.prototype.slice.call(arguments));
		
		function thisInit()
		{
			$(this)
				.find("img")
				.css({position: "absolute", left: 0, top: 0});
			
			return $(this)
				.uniqueId()
				.css({position: "relative", "z-index": 0})
				.attr("imageIndex", 0)
				.each(function(){
					_CrossSliderSliders[$(this).attr("id")] = $(this);
				});
		}
		function thisShowNext()
		{
			var images	= $(this).find('img');
			
			var index = parseInt($(this).attr("imageIndex"));
			var newIndex = (index + 1) % images.length;
			if (index == newIndex) return;
			
			var now = $(images.get(index));
			var next = $(images.get(newIndex));
			
			$(this).attr("imageIndex", newIndex);
			
			next.css('z-index',2).show();
			now.css('z-index',3)
				.fadeOut(1500,function(){
					now.hide();
				});
		}
	};
})();

function CrossSlideNextImage()
{
	for(var slider in _CrossSliderSliders)
	{
		_CrossSliderSliders[slider].CrossSlide("showNext");
	}
	
	setTimeout('CrossSlideNextImage()', 8000);
}
setTimeout('CrossSlideNextImage()', 1000);
