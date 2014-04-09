<?
function module_style(&$val)
{
	$style = &$GLOBALS['_SETTINGS']['style'][$val];
	if (!is_null($style)) return;
	$style = '';
	
	$fn = getFn("style_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	$style	= ob_get_clean();
}
function module_script(&$val)
{
	$script = &$GLOBALS['_SETTINGS']['script'][$val];
	if (!is_null($script)) return;
	
	$script = '';
	$fn		= getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	
	//	Присоеденить стиль, если есть такой
	m("style:$val");
	
	ob_start();
	if ($fn) $fn($val);
	//	Для сохранения зависимостей скрипты вызванные ранее должны быть первыми
	unset($GLOBALS['_SETTINGS']['script'][$val]);
	//	Пересоздать значение
	$GLOBALS['_SETTINGS']['script'][$val]	= ob_get_clean();
}
function module_scriptLoad(&$val, &$data)
{
	if (!$data) return;
	$GLOBALS['_SETTINGS']['scriptLoad'][$data] = $data;
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
<? m('scriptLoad', "script/$ver"); ?>
<? } ?>

<? function script_jq_ui($val){
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= @$ini[':']['jQueryUI'];
	
	$jQuery	= getCacheValue('jQuery');
	$ver	= $jQuery['jQueryUIVersion'];
	if (!$uiTheme) $uiTheme=$jQuery['jQueryUIVersionTheme'];
	m('page:style', "script/$ver/css/$uiTheme/$ver.min.css");
?>
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
<? m('scriptLoad', "script/$ver/js/$ver.min.js") ?>
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.cookie.min.js"></script>
<? } ?>

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

<? function script_CrossSlide($val){
	m('script:jq');
	m('script::load', "script/jquery.cross-slide.min.js");
} ?>

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

