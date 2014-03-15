<? function script_seekKey($val){ m('script:jq'); ?>
<script>
$(function(){
	$(document).keydown(function(){
		var href = '';
		var ch = (event.keyChar == null) ? event.keyCode : event.keyChar;
		
		switch(ch){
			case 39:
				href = $(".seek #nav2").attr("href");
				break;
			case 37:
				href = $(".seek #nav").attr("href");
				break;
			default:
				return;
		};
		if (href) document.location = href;
	});
});
</script>
<? } ?>