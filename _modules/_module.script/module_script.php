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
?>
<?
function script_jq($val){
	$ver = getCacheValue('jQueryVersion');
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
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cookie.min.js"></script>
<? } ?>

<?
function script_overlay($val){
	module('script:jq');
?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function(overlayClass) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer").remove();
		$("#fadeOverlayHolder").remove();
		var overlay = $('<div id="fadeOverlayLayer" />')
			.appendTo('body')
			.css({
				'position': 'fixed',
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
				'opacity': 0.8,
				'background': 'black'
				})
			.click(function(){
				$("#fadeOverlayLayer").remove();
				$("#fadeOverlayHolder").remove();
			});
		if (overlayClass) $('<div />').addClass(overlayClass).appendTo('body').click(function(){
			$("#fadeOverlayLayer").remove();
			$("#fadeOverlayHolder").remove();
			$(this).remove();
		});
		return $('<div id="fadeOverlayHolder" />').appendTo('body').append(this);
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
<script type="text/javascript" language="javascript">
$(function(){
	$('[id*="calendar"]').datepicker({
		dateFormat: 	'dd.mm.yy',
		monthNames: 	['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort:['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сент','Окт','Ноя','Дек'],
		dayNamesMin: 	['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
		firstDay: 		1});
});
</script>
<? } ?>

<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="script/lightbox/css/jquery.lightbox-0.5.css"/>
<script type="text/javascript" src="script/lightbox/jquery.lightbox-0.5.js"></script>
<script type="text/javascript">
$(function(){
	$("a[rel='lightbox']").lightBox().removeAttr("rel");
});
</script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cross-slide.min.js"></script>
<? } ?>

<? function script_menu($val){ module('script:jq'); ?>
<script type="text/javascript">
//	menu
var menuTimer = 0;
$(function() {
	$('.menu.popup > li, .menu.popup td').hover(function(){
		clearTimeout(menuTimer);
		menuTimer = 0;
		$(".menu.popup ul").hide();
		$(this).find("ul").show().css({top: $(this).position().top+$(this).height(), left: $(this).position().left});
	}, function(){
		clearTimeout(menuTimer);
		menuTimer = setTimeout(popupMenuClose, 500);
//		$(".menu.popup ul").hide();
	});
});
function popupMenuClose(){
	clearTimeout(menuTimer);
	menuTimer = 0;
	$(".menu.popup ul").hide();
}
</script>
<? } ?>

<? function script_ajaxLink($val){ module('script:overlay'); m('page:style', 'ajax.css') ?>
<script type="text/javascript" language="javascript">
$(function(){
/*<![CDATA[*/
	$('a[id*="ajax"]').click(function()
	{
		var id = $(this).attr('id');
		$('<div />').overlay('ajaxLoading')
			.css({position:'absolute', top:0, left:0, right:0})
			.load($(this).attr('href'), 'ajax=' + id);
		return false;
	});
 /*]]>*/
});
</script>
<? } ?>
<? function script_ajaxForm($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	//	Отправка через AJAX, только если есть overlay
	$(".ajaxForm").submit(function(){
		if ($('#fadeOverlayHolder').length == 0) return true;
		return submitAjaxForm($(this));
	}).removeClass("ajaxForm").addClass("ajaxSubmit");
	
	$(".ajaxFormNow").submit(function(){
		return submitAjaxForm($(this));
	}).removeClass("ajaxForm").addClass("ajaxSubmit");
});

function submitAjaxForm(form)
{
	if (form.hasClass('submitPending')) return;
	form.addClass('submitPending');
	
	$('#formReadMessage').remove();
	$('<div id="formReadMessage" class="message work">')
		.insertBefore(form)
		.html("Обработка данных сервером, ждите.");

	var ajaxForm = form.hasClass('ajaxSubmit')?'ajax_message':'';
	if (form.hasClass('ajaxReload')) ajaxForm = 'ajax';

	var formData = form.serialize();
	if (ajaxForm) formData += "&ajax=" + ajaxForm;

	$.post(form.attr("action"), formData)
		.success(function(data){
			form.removeClass('submitPending');
			if (form.hasClass('ajaxReload')){
				$('#fadeOverlayHolder').html(data);
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
		$(this).parents("tr").remove();
		return false;
	});
});
function adminCloneByID(id)
{
	var o = $(".adminReplicate#" + id);
	o.clone().insertBefore(o).removeClass("adminReplicate");
	$(".adminReplicate#" + id + " input").val("");
	
	$('a.delete').click(function(){
		$(this).parents("tr").remove();
		return false;
	});
}
 /*]]>*/
</script>
<? } ?>


