<? function script_overlay($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function(overlayClass, closeSelector) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		$('<div id="fadeOverlayLayer" />')
			.appendTo('body')
			.css({
				'position': 'fixed', 'z-index':100,
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
				'opacity': 0.8, 'background': 'black'
				});
				
		$('<div id="fadeOverlayHolder" />')
			.appendTo('body')
			.addClass(overlayClass)
			.append($(this))
			.css({
				'position': 'fixed', 'z-index':100,
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0
				});
			if (closeSelector){
				$(closeSelector).click(function(){
					var ctx = $($("#fadeOverlayHolder").html());
					$('body').removeClass("ajaxOverlay").append(ctx);
					ctx.hide();
					$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
					return false;
				});
			}
		$("body").addClass("ajaxOverlay");
		return $(this);
   };
})( jQuery );
 /*]]>*/
</script>
<? } ?>
