// JavaScript Document

$(function()
{
	$(document).on("jqReady ready", function()
	{
		$("form.ajaxFormNow, form.ajaxForm, form.ajaxFormReload, form.ajaxSubmit, .ajaxForm form")
		.each(function()
		{
			//	Make ajax template name
			var ajaxForm = 'ajax_message';
			if ($(this).hasClass('ajaxReload'))
			{
				if ($('#fadeOverlayHolder').length > 0){
					ajaxForm = $(this).attr('id');
					if (!ajaxForm) $("body").attr("ajaxTemplateName");
					if (!ajaxForm) ajaxForm = 'ajax';
				}else{
					ajaxForm = '';
				}
			}
	
			if (ajaxForm){
				var action = $(this).attr("action");
				action += action.indexOf("?")>0?'&':'?';
				$(this).attr("action", action + 'ajax=' + ajaxForm);
			}
	
			var options = {
				start:	function(form){
					submitAjaxForm($(this));
				},
				end: 	function(data)
				{
					if ($(this).hasClass('ajaxReload')){
						$('#formReadMessage').remove();
						$('#fadeOverlayHolder').html(data);
						$(document).trigger("jqReady");
					}else{
						$('#formReadMessage')
							.removeClass("message").removeClass("work")
							.html(data);
					}
				}
			};
			if ($('#fadeOverlayHolder').length == 0){
				options["target"]	= "";
				return;
			};
			
			$(this).ajaxForm(options);
	
		});
	});
});
function submitAjaxForm(form)
{
	//	Print loading message
	var msg = $('#formReadMessage');
	if (msg.length == 0) msg = $('<div id="formReadMessage" class="message work">').insertBefore(form);
	msg.addClass("message work").html("Обработка данных сервером, ждите.");
}

(function( $ )
{	//	AJAX form over hidden IFRAME submit
	$.fn.ajaxForm = function(options, callback)
	{
		var opts = $.extend( {}, $.fn.ajaxForm.defaults, options );
		if (typeof(options) == 'function'){
			opts.start = options;
		}
		if (typeof(callback) == 'function'){
			opts.end = callback;
		}
		
		$(this)
		.unbind("submit.ajaxForm")
		.on("submit.ajaxForm", function()
		{
			var thisForm = $(this);
			if (thisForm.hasClass("submitPending")) return;
			thisForm.addClass("submitPending");
			opts.start.call(thisForm);

			var encType = "" + $(this).attr("enctype");
			if (opts.target && encType.toLowerCase() == "multipart/form-data")
			{
				if ($("#" + opts.target).length == 0)	$('body')
					.append('<iframe name="'+opts.target+'" id="'+opts.target+'" style="display:none"></iframe>')
				
				thisForm.attr("target", opts.target)
					
				$("#" + opts.target).unbind().load(function()
				{
					thisForm.removeClass("submitPending");
					var responce = $(this).contents().find("html");
					opts.end.call(thisForm, responce.html());
				});
				return true;
			}else{
				$.post($(this).attr("action"), $(this).serialize())
				.success(function(data, status){
					thisForm.removeClass("submitPending");
					opts.end.call(thisForm, data);
				});
				return false;
			}
		});
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.ajaxForm.defaults = 
	{
		target:	"formUploadFrame",
		start:	function() {},
		end:	function() {}
	};
})(jQuery);
