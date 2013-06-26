<?
function module_script($val)
{
	$GLOBALS['_SETTINGS']['script'][$val] = true;
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
	if (isModernBrowser()) $ver = getCacheValue('jQueryVersion2');
	else $ver = getCacheValue('jQueryVersion');
?>
<? if (testValue('ahax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery == 'undefined'){  
  document.write('<' + 'script type="text/javascript" src="script/<?= $ver ?>"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/<?= $ver ?>"></script>
<? } ?>

<? function script_jq_ui($val){
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= @$ini[':']['jQueryUI'];
	
	$ver	= getCacheValue('jQueryUIVersion');
	if (!$uiTheme) $uiTheme= getCacheValue('jQueryUIVersionTheme');
?>
<link rel="stylesheet" type="text/css" href="script/<?= $ver?>/css/<?= $uiTheme ?>/<?= $ver?>.min.css"/>
<? if (testValue('ahax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery.ui == 'undefined') {
	 document.write('<' + 'script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script>
<? } ?>

<? function script_jq_print($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.printElement.min.js"></script>
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
<script type="text/javascript" src="script/jquery.cookie.min.js"></script>
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
		return $('<div id="fadeOverlayHolder" />').appendTo('body').css({'z-index':50});
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

<? function script_calendar($val){ module('script:jq_ui'); ?>
<script type="text/javascript" src="script/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" language="javascript">
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('[id*="calendar"], .calendar').each(function(){
			attachDatetimepicker($(this));
		});
	});
});
function attachDatetimepicker(o){
	o.datetimepicker({
		dateFormat: 	'dd.mm.yy',
		monthNames: 	['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort:['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сент','Окт','Ноя','Дек'],
		dayNamesMin: 	['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
		firstDay: 		1,
		timeOnlyTitle: 'Выберите время',
		timeText: 'Время',
		hourText: 'Часы',
		minuteText: 'Минуты',
		secondText: 'Секунды',
		currentText: 'Теперь',
		closeText: 'Закрыть'
		});
}
</script>
<? } ?>

<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="script/lightbox2.51/css/lightbox.css"/>
<script type="text/javascript" src="script/lightbox2.51/js/lightbox.js"></script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cross-slide.min.js"></script>
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

<? function script_ajaxLink($val){ module('script:overlay'); m('page:style', 'ajax.css') ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('a[id*="ajax"]').click(function(){
			return ajaxLoad($(this).attr('href'), 'ajax=' +  $(this).attr('id'));
		});
		ajaxClose();
		var data = $("#fadeOverlayHolder").attr("rel");
		if (data){
			$(".ajaxDocument .seek a").click(function(){
				return ajaxLoad($(this).attr('href'), data);
			});
		}
	});
});
function ajaxClose(){
	$(".ajaxClose a").click(function()
	{
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		return false;
	});
}
function ajaxLoad(url, data)
{
	$('<div />').overlay('ajaxLoading')
		.css({position:'absolute', top:0, left:0, right:0, bottom: 0})
		.attr("rel", data)
		.load(url, data, function()
		{
			$(".ajaxLoading").remove();
			ajaxClose();
			$(document).trigger("jqReady");
		});
	return false;
}
 /*]]>*/
</script>
<? } ?>
<? function script_ajaxForm($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		//	Отправка через AJAX, только если есть overlay
		$(".ajaxForm").submit(function(){
			if ($('#fadeOverlayHolder').length == 0) return true;
			return submitAjaxForm($(this));
		}).removeClass("ajaxForm").addClass("ajaxSubmit");
		
		$(".ajaxFormNow").submit(function(){
			return submitAjaxForm($(this));
		}).removeClass("ajaxFormNow").addClass("ajaxSubmit");
	});
});

function submitAjaxForm(form, bSubmitNow)
{
	form = $(form);
	if (!bSubmitNow && form.find(".submitEditor").length > 0) return;
	if (("" + form.attr("enctype")).toLowerCase() == "multipart/form-data") return;
	
	var msg = $('#formReadMessage');
	if (msg.length == 0) msg = $('<div id="formReadMessage" class="message work">').insertBefore(form);
	msg.addClass("message work").html("Обработка данных сервером, ждите.");

	if (form.hasClass("ajaxReload") && $('#fadeOverlayHolder').length == 0) return true;
	if (form.hasClass('submitPending')) return;
	form.addClass('submitPending');
	
	var ajaxForm = form.hasClass('ajaxSubmit')?'ajax_message':'';
	if (form.hasClass('ajaxReload')) ajaxForm = 'ajax';

	var formData = form.serialize();
	if (ajaxForm) formData += "&ajax=" + ajaxForm;

	$.post(form.attr("action"), formData)
		.success(function(data){
			form.removeClass('submitPending');
			if (form.hasClass('ajaxReload')){
				$('#fadeOverlayHolder').html(data);
				$(document).trigger("jqReady");
			}else{
				$('#formReadMessage')
					.removeClass("message")
					.removeClass("work")
					.html(data);
			}
		})
		.error(function(){
			form.removeClass('submitPending');
			$('#formReadMessage')
				.removeClass("work")
				.addClass("error")
				.html("Ошибка записи");
		});
	return false;
};
 /*]]>*/
</script>
<? } ?>
<? function script_scroll($val){?>
<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
$(function(){
	$(".scroll").css({"height":$(".scroll table").height(), "overflow":"hidden"})
	.mousemove(function(e)
	{
		//	over
		var cut = 80;
		var thisWidth = $(this).width();
		var width = $(this).find("table").width();
		if (width < thisWidth) return;
		var widthDiff = width - thisWidth;
	
		var percent = (e.pageX - ($(this).offset().left + cut))/(thisWidth - cut*2);
		if (percent < 0) percent = 0;
		if (percent > 1) percent = 1;
		$(this).find("table").css("left", -Math.round(percent*widthDiff));
	});
});
 /*]]>*/
</script>
<? } ?>

<? function script_maskInput($val){ module('script:jq')?>
<script type="text/javascript" src="script/jquery.maskedinput.min.js"></script>
<script>
$(function(){
	$("input.phone").mask("+7(999) 999-99-99");
});
</script>
<? } ?>

<? function script_clone($val){?>
<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
$(function(){
	$("input.adminReplicateButton").click(function(){
		return adminCloneByID($(this).attr('id'));
	}).removeClass("adminReplicateButton");
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
});
function adminCloneByID(id)
{
	var o = $(".adminReplicate#" + id);
	var o2 = o.clone().insertBefore(o).removeClass("adminReplicate");
	$(o2.find(".hasDatepicker")).each(function(){
		$(this).removeClass("hasDatepicker").attr("id", Math.random(20000000));
		attachDatetimepicker($(this));
	});
	
	$(".adminReplicate#" + id + " input").val("");
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
}
 /*]]>*/
</script>
<? } ?>


