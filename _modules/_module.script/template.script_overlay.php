<? function script_overlay($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function( options )
  {
	switch(options){
	case 'close':
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		$('body').removeClass("ajaxOverlay");
		return $(this);
	case 'show':
		$("#fadeOverlayLayer,#fadeOverlayHolder").show();
		return $(this);
	case 'hide':
		$("#fadeOverlayLayer,#fadeOverlayHolder").hide();
		return $(this);
	}
	
	var settings = $.extend({
		class: "",
	}, options );

	if (typeof(options) == 'string') settings.class = options;
	
	// Create overlay and append to body:
	$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
	$('<div id="fadeOverlayLayer" />').appendTo('body')
		.css({
			'position': 'fixed', 'z-index':100,
			'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
			'opacity': 0.8, 'background': 'black'
			});
			
	$('<div id="fadeOverlayHolder" />')
		.appendTo('body').addClass(settings.class).append($(this))
		.css({'position': 'fixed', 'z-index':100, 'top': 0, 'left': 0, 'right': 0, 'bottom': 0 });
		
	$("body").addClass("ajaxOverlay");
	return $(this);
   };
})( jQuery );
 /*]]>*/
</script>
<? } ?>
