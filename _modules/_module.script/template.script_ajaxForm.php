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
