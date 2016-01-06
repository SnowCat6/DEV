// JavaScript Document

$(function(){
	$(document).on("jqReady ready", function()
	{
		$('a[id*="ajax"]')
		.unbind("click.ajaxLoad")
		.on("click.ajaxLoad", function(){
			var ajaxTemplateName = $(this).attr('id');
			$("body").attr("ajaxTemplateName", ajaxTemplateName);
			return ajaxLoad($(this).attr('href'));
		});

		$(".ajaxBody .seek a, .ajaxBody .seekLink a, .ajaxBody a.seekLink")
		.not("a[href=#]")
		.unbind("click.ajaxLoad")
		.on("click.ajaxLoad", function(){
			if ($(this).hasClass("notLink")) return;
			return ajaxLoad($(this).attr('href'));
		});

		ajaxClose();
	});
});
function ajaxClose(){
	$(".ajaxClose")
	.unbind("click.ajaxLoad")
	.on("click.ajaxLoad", function()
	{
		$(this).overlay("close");
		return false;
	});
}
function ajaxLoadPage(url){
	if ($("#fadeOverlayHolder").size() == 0){
		document.location = url;
		return;
	};
	return ajaxLoad(url);
}
function ajaxLoad(url)
{
	var data = 'ajax=' + $("body").attr("ajaxTemplateName");
	
	$('<div />')
		.overlay('ajaxLoading')
		.load(url, data, function(http, status)
		{
			$(".ajaxLoading").removeClass("ajaxLoading");
			ajaxClose();
			$(document).trigger("jqReady");
		});
	return false;
}
