<? function script_overlay($val){ module('script:jq'); ?>
<script>
(function( $ )
{
	$.fn.overlay = function(method, options )
	{
		var methods = {
			init:	thisInit,		//	Init method
			message:thisMessage,	//	Show message box
			show:	thisShow,		//	Show
			hide:	thisHide,		//	Hide
			close:	thisClose,		//	Clode
		};
		
		return methods[method]?
			methods[method].apply(this, Array.prototype.slice.call(arguments, 1)):
			methods['init'].apply(this, Array.prototype.slice.call(arguments));

		function thisShow(){
			thisLayers().show();
			return this;
		}
		function thisHide(){
			thisLayers().hide();
			return this;
		}
		function thisClose(){
			thisLayers().remove();
			$('body').removeClass("ajaxOverlay");
			return this;
		}
		function thisMessage(message){
			$('<div class="ajaxOverlay ajaxMessage"><div class="message">' + message + '</div></div>')
			.overlay()
			.append($(this));
		}
		function thisInit(options)
		{
			var settings = $.extend({}, $.fn.overlay.defaults, options );
			if (typeof(options) == 'string') settings.class = options;
		
			// Create overlay and append to body:
			thisLayers().remove();
			
			$('<div id="fadeOverlayLayer" />')
				.css(settings.cssOverlay)
				.appendTo('body');
					
			$('<div id="fadeOverlayHolder" />')
				.addClass(settings.class)
				.css(settings.cssHolder)
				.appendTo('body')
				.append(this);
				
			$("body").addClass("ajaxOverlay");
			
			return this;
		};
		function thisLayers(){
			return $("#fadeOverlayLayer, #fadeOverlayHolder");
		}
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.overlay.defaults = 
	{
		class:		"",
		cssHolder:	{
			'position': 'fixed', 'z-index':100,
			'top': 0, 'left': 0, 'right': 0, 'bottom': 0 
		},
		cssOverlay:	{
			'position': 'fixed', 'z-index':100,
			'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
			'opacity': 0.8, 'background': 'black'
		},
	};
})( jQuery );
</script>
<? } ?>
