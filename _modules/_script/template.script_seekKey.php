<? function script_seekKey($val){ m('script:jq'); ?>
<script>
$(function(){
	$(document).keydown(function()
	{
		var seekName = "";
		var ch = (event.keyChar == null) ? event.keyCode : event.keyChar;
		
		switch(ch){
			case 37:	seekName = ".seek #nav";	break;
			case 39:	seekName = ".seek #nav2";	break;
		};
		if (!seekName) return;
		
		var seek = null;
		if ($("#fadeOverlayHolder").length){
			seek = $("#fadeOverlayHolder " + seekName);
		}else{
			seek = $(seekName);
		}
		if (seek[0] == null) return;
		seek[0].click();
	});
});
</script>
<? } ?>