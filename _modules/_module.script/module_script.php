<?
function module_script($val){
	$fn = getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	module("page:script:$val", ob_get_clean());
}
?>
<?
function script_jq($val){
	if (testValue('ajax')) return;
	$ver = getCacheValue('jQueryVersion');
?>
<script type="text/javascript" src="script/<?= $ver ?>"></script>
<? } ?>

<? function script_jq_ui($val){
	module('script:jq');
	$ver	= getCacheValue('jQueryUIVersion');
	$uiTheme= getCacheValue('jQueryUIVersionTheme');
?>
<script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script>
<link rel="stylesheet" type="text/css" href="script/<?= $ver?>/css/<?= $uiTheme ?>/<?= $ver?>.min.css"/>
<? } ?>

<? function script_jq_print($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.printElement.min.js"></script>
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cookie.min.js"></script>
<? } ?>

<?
function script_overlay($val){
	if (testValue('ajax')) return;
	module('script:jq');
?>
<script type="text/javascript" language="javascript">
(function( $ ) {
  $.fn.overlay = function(closeFn) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer").remove();
		$("#fadeOverlayHolder").remove();
		var overlay = $('<div id="fadeOverlayLayer"/>')
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

		return $('<div id="fadeOverlayHolder" />').appendTo('body').append(this);
  };
})( jQuery );
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

<? function script_datepicker($val){ module('script:jq_ui'); ?>
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
	$("a[rel='lightbox']").lightBox();
});
</script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cross-slide.min.js"></script>
<? } ?>

<? function script_menu($val){ module('script:jq'); ?>
<script type="text/javascript">
//	menu
$(function() {
	$('.menu.popup > li').hover(function(){
		$(".menu.popup ul").hide();
		$(this).find("ul").show();
	}, function(){
		$(".menu.popup ul").hide();
	});
});
</script>
<? } ?>

<? function script_ajaxLink($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
$(function(){
	$('a[id*="ajax"]').click(function()
	{
		var id = $(this).attr('id');
		$('<div />').overlay()
			.css({position:'absolute', top:0, left:0, right:0})
			.load($(this).attr('href'), 'ajax=' + id);
		return false;
	});
});
</script>
<? } ?>
<? function script_ajaxForm($val){ ?>
<script type="text/javascript" language="javascript">
$(function(){
	$(".ajaxForm").submit(function(){
		return submitAjaxForm($(this));
	}).removeClass("ajaxForm").addClass("ajaxSubmit");
});
function submitAjaxForm(form)
{
	$('#formReadMessage').remove();
	$('<div id="formReadMessage" class="message work">')
		.insertBefore(form)
		.html("Обработка данных сервером, ждите.");
		
	$.post(form.attr("action"), form.serialize() + "&ajax=ajax_message")
		.success(function(data){
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
			$('#formReadMessage')
				.removeClass("work")
				.addClass("error")
				.html("Ошибка записи");
		});
	return false;
};
</script>
<? } ?>



