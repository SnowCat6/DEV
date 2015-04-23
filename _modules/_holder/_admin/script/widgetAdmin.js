// JavaScript Document

$(function(){
	$(document).on("widgetUpdate", function(e, p)
	{
		$.ajax("admin_widgetLoad.htm?id=" + p)
		.done(function(data)
		{
			$().overlay("close");
			$(".adminWidget#" + p).html(data);
			$(document).trigger("jqReady");
		});
	});
});