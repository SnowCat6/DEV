// JavaScript Document

$(function(){
	$(".radioFilter input").change(function(){
		$(this).parents("form").submit();
	});
});

