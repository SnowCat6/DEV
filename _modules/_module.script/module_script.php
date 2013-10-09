<?
function module_script($val)
{
	$script = &$GLOBALS['_SETTINGS']['script'][$val];
	if ($script) return;
	$script = true;
	
	$fn = getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	module("page:script:$val", ob_get_clean());
}
function hasScriptUser($val){
	return @$GLOBALS['_SETTINGS']['script'][$val];
}
function isModernBrowser()
{
	$agent		= strtolower($_SERVER['HTTP_USER_AGENT']);
	$browsers	= array("firefox", "opera", "chrome", "safari"); 
	foreach($browsers as $browser){
		if (strpos($agent, $browser)) return true;
	}
	return false;
}
?>
<?
function script_jq($val){
	$jQuery	= getCacheValue('jQuery');
	if (isModernBrowser()) $ver = $jQuery['jQueryVersion2'];
	else $ver = $jQuery['jQueryVersion'];
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery == 'undefined'){  
	document.write('<' + 'script type="text/javascript" src="<?= globalRootURL?>/script/<?= $ver ?>"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/<?= $ver ?>"></script>
<? } ?>

<? function script_jq_ui($val){
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= @$ini[':']['jQueryUI'];
	
	$jQuery	= getCacheValue('jQuery');
	$ver	= $jQuery['jQueryUIVersion'];
	if (!$uiTheme) $uiTheme=$jQuery['jQueryUIVersionTheme'];
?>
<link rel="stylesheet" type="text/css" href="<?= globalRootURL?>/script/<?= $ver?>/css/<?= $uiTheme ?>/<?= $ver?>.min.css"/>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof jQuery.ui == 'undefined'){
		$.getScript('<?= globalRootURL?>/script/<?= $ver?>/js/<?= $ver?>.min.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/<?= $ver?>/js/<?= $ver?>.min.js"></script>
<? } ?>

<? function script_jq_print($val){ module('script:jq'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.printElement.min.js"></script>
<script>
/*<![CDATA[*/
	jQuery.browser = {};
	jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
 /*]]>*/
</script>
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.cookie.min.js"></script>
<? } ?>

<? function script_overlay($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function(overlayClass) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		var overlay = $('<div id="fadeOverlayLayer" />').appendTo('body')
			.css({
				'position': 'fixed', 'z-index':50,
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
				'opacity': 0.8, 'background': 'black'
				})
			.click(function(){
				$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
			});
		if (overlayClass) $('<div />').addClass(overlayClass).appendTo('body').click(function(){
			$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
			$(this).remove();
		});
		return $('<div id="fadeOverlayHolder" />').appendTo('body').css({'z-index':51});
   };
})( jQuery );
 /*]]>*/
</script>
<? } ?>

<? function script_center($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
(function( $ ) {
	$.fn.center = function() {
		this.css("position","absolute");
		this.css("top",	Math.max(0, (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop()) + "px");
		this.css("left",Math.max(0, (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft()) + "px");
		return this;
	};
})( jQuery );
</script>
<? } ?>

<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="<?= globalRootURL?>/script/lightbox2.51/css/lightbox.css"/>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof lightbox == 'undefined'){
		$.getScript('<?= globalRootURL?>/script/lightbox2.51/js/lightbox.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/lightbox2.51/js/lightbox.js"></script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.cross-slide.min.js"></script>
<? } ?>

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

<? function script_maskInput($val){ module('script:jq')?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.maskedinput.min.js"></script>
<script>
$(function(){
	$("input.phone").mask("+7(999) 999-99-99");
});
</script>
<? } ?>
