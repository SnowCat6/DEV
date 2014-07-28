<? function script_menu($val){ module('script:jq'); ?>
<script type="text/javascript">
//	menu
var menuTimer = 0;
$(function() {
	$('.menu.popup ul li, .menu.popup td').hover(function(){
		popupMenuClose();
		$(this).find("ul").show().css({top: $(this).position().top+$(this).height(), left: $(this).position().left});
	}, function(){
		clearTimeout(menuTimer);
		menuTimer = setTimeout(popupMenuClose, 500);
	});
	$(".menu.popup ul ul li, .menu.popup td li").unbind();
});
function popupMenuClose(){
	$(".menu.popup li ul, .menu.popup td ul").hide();
	clearTimeout(menuTimer);
	menuTimer = 0;
}
</script>
<? } ?>
