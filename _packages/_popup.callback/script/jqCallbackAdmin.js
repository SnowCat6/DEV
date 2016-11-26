// JavaScript Document

$(function(){
	$("input[rel*=advStyle]").keyup(function()
	{
		var val = $(this).val();
		if (!val) val = $(this).attr('placeholder');

		var name = $(this).attr("rel").split(':', 2)[1];
		
		$(".callbackAdvAdmin .callbackAdv")
		.css(name, val);
	});
});