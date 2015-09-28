// JavaScript Document
$(function(){
	$(".feedbackForm").submit(function(){
		var bOK = true;
		$(this).find(".fieldMustBe input, .fieldMustBe select, .fieldMustBe textarea")
		.each(function(){
			if ($(this).val()) return;
			if (bOK) $(this).focus().addClass('doImputField');
			bOK = false;
		})
		return bOK;
	})
	.find(".fieldMustBe input, .fieldMustBe select, .fieldMustBe textarea")
		.attr("title", "Обязательное для заполениия поле").tooltip()
		.keydown(function(){
			$(this).removeClass('doImputField').tooltip("close");
		});
});
