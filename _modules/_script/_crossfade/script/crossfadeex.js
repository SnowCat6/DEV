(function( $ )
{
	$.fn.CrossFadeEx = function(method)
	{
		var methods = {
			init:	thisInit,		//	Init method
			select:	thisSelect,
			start:	thisStart,
			stop:	thisStop
		};
		
		return methods[method]?
			methods[method].apply(this, Array.prototype.slice.call(arguments, 1)):
			methods['init'].apply(this, Array.prototype.slice.call(arguments));
		//////////////////////////////
		function thisInit(options)
		{
			var opts = $.extend( {}, $.fn.CrossFadeEx.defaults, options);

			return $(this)
			.data('CrossFadeEx', opts)
			.addClass('CrossFadeEx')
			.each(function(){
				seekCreate($(this));
			});
		}
		function thisSelect(index){
			return $(this).each(function(){
				seekSet($(this), index);
			});
		}
		function thisStart(index){
			return $(this).each(function(){
				seekSetTimeout($(this), true);
			});
		}
		function thisStop(index){
			return $(this).each(function(){
				seekSetTimeout($(this), false);
			});
		}
		/************************/
		function seekCreate(holderElm)
		{
			var childs = seekItems(holderElm);
			if (childs.length < 2) return;
			
			var opts= holderElm.data('CrossFadeEx');
			holderElm.attr("indexMax", childs.length);
			
			//	SEEK
			switch(holderElm.data("CrossFadeEx").seek)
			{
			case 'image':	seekCreateElmImage(holderElm, childs);
				break;
			case 'dot':		seekCreateElmDot(holderElm, childs);
				break;
			case 'none':	seekCreateElmNone(holderElm, childs);
				break;
			}
			
			//	BUTTON
			if (opts.buttons)
				seekCreateButtons(holderElm);
			//	BUTTON ACTION
			holderElm.find(".seekPrev")
			.click(function(){
				seekSet(holderElm, "-1");
			});
			holderElm.find(".seekNext")
			.click(function(){
				seekSet(holderElm, "+1");
			});
		
			seekSet(holderElm, 0);
			
			if (opts.autostart)
				seekSetTimeout(holderElm, true);
			
			holderElm
			.hover(function(){
				seekSetTimeout($(this), false);
			}, function(){
				seekSetTimeout($(this), true);
			});

		}
		/************************/
		function seekCreateHolder(holderElm, html)
		{
			holderElm.append('<div class="seekHolder">' + html + '</div>');
			var seekHolder = $(holderElm.find(".seekHolder"));
			//	HOLDER POSITION
			seekHolder.css({
				position:	"absolute",
				"z-index":	3,
				bottom:		20,
				left:		'50%',
			}).css({
				"margin-left":	Math.round(-seekHolder.width() / 2)
			})
			.children()
			.hover(function(){
				seekSet(holderElm, $(this).attr("index"));
			})
			;
			return seekHolder;
		}
		/************************/
		function seekCreateElmImage(holderElm, childs)
		{
			var html = '';
			var opts 		= holderElm.data('CrossFadeEx');
			for(ix = 0; ix < childs.length; ++ix){
				html += '<div class="seekElm seekImage" index="' + ix + '">' + (opts.showNum?'<div class="seekStyle">' + (ix+1) + '</div>':'') + '</div>';
			}

			var seekHolder = seekCreateHolder(holderElm, html);
			for(ix = 0; ix < childs.length; ++ix)
			{
				var img = $(childs.get(ix)).find("img");
				if (img.length < 1) return;
		
				$(seekHolder.children().get(ix))
				.attr("title", img.attr("title"))
				.css({
					"background-image": "url(" + img.attr("src") + ")",
					"background-size": "cover"
				});
			};
		}
		function seekCreateElmDot(holderElm, childs)
		{
			var html = '';
			var opts = holderElm.data('CrossFadeEx');
			for(ix = 0; ix < childs.length; ++ix){
				html += '<div class="seekElm seekDot seekStyle" index="' + ix + '">' + (opts.showNum?ix+1:'') + '</div>';
			}
			seekCreateHolder(holderElm, html);
		}
		function seekCreateElmNone(holderElm, seekHolder, childs)
		{
		}
		/************************/
		function seekCreateButtons(holderElm)
		{
			holderElm.append('<div class="seekHolderButtons"><div class="seekButton seekPrev seekStyle"></div><div class="seekButton seekNext seekStyle"></div></div>');
		}
		/************************/
		function seekSet(holderElm, thisIndex)
		{
			var opts 		= holderElm.data('CrossFadeEx');
			var seekHolder	= holderElm.find(".seekHolder");
			if (String(thisIndex)[0] == '+' || String(thisIndex)[0] == '-'){
				thisIndex = parseInt(holderElm.attr("index")) + parseInt(thisIndex);
			}
			thisIndex		= parseInt(thisIndex) % parseInt(holderElm.attr("indexMax"));
			var prevIndex	= holderElm.attr("index");
			if (thisIndex == prevIndex) return;
			
			holderElm.attr("index", thisIndex);
			
			var items		= seekItems(holderElm);
			var thisSeek	= $(seekHolder.children().get(thisIndex));
			var thisItem	= $(items.get(thisIndex));
			
			thisSeek.add(thisItem).addClass("current");
				
			seekHolder.children().add(holderElm.children())
			.not(thisSeek).not(thisItem)
			.removeClass("current");
			
			if (typeof prevIndex == 'undefined'){
				items.not(thisItem)
				.css({"z-index": 0, visibility: 'hidden'});
				return thisItem.css({"z-index": 2, opacity: 1});
			}

			var prevElm	= $(items.get(prevIndex));
			
			if (holderElm.data("CrossFadeExAnimate"))
			{
				items
				.stop(true, false)
				.not(thisItem)
				.css({"z-index": 0, visibility: 'hidden'});
				
				thisItem.css({"z-index": 2, opacity: 1, visibility: 'visible'});
				holderElm.data("CrossFadeExAnimate", false);
				return;
			}
			holderElm.data("CrossFadeExAnimate", true);
			
			items
			.not(prevElm).not(thisItem)
			.css({"z-index": 0, visibility: 'hidden'});
			
			prevElm.add(thisItem)
			.css({"z-index": 1, visibility: 'visible'});
			
			thisItem
			.css({"z-index": 2, opacity: 0})
			.animate({opacity: 1}, opts.fade, function(){
				prevElm.css({visibility: 'hidden'});
				holderElm.data("CrossFadeExAnimate", false);
			});
		}
		/************************/
		function seekItems(holderElm)
		{
			var opts = holderElm.data('CrossFadeEx');
			if (opts.items) return holderElm.find(opts.items);
			return holderElm.children().not(holderElm.find(".seekHolderButtons, .seekHolder"));
		}
		/************************/
		function seekSetTimeout(holderElm, bSetTimeout)
		{
			var seekTimeout = holderElm.data("CrossFadeExTimeout");
			if (seekTimeout) clearTimeout(seekTimeout);

			seekTimeout = bSetTimeout?setTimeout(function()
			{
					seekSet(holderElm, "+1");
					seekSetTimeout(holderElm, true);
				},	holderElm.data('CrossFadeEx').speed):0;

			holderElm.data("CrossFadeExTimeout", seekTimeout);
		}
	}
	// Plugin defaults – added as a property on our plugin function.
	$.fn.CrossFadeEx.defaults = 
	{
		speed: 3 * 1000,
		autostart: true,
		fade: 500,
		seek: 'dot',
//		seek: 'image',
//		seek: 'none',
		showNum: false,
		buttons: true,
		items:	""
	};
	$.fn.CrossSlideEx= $.fn.CrossFadeEx;
	$.fn.CrossSlide	 = $.fn.CrossFadeEx;
})( jQuery );