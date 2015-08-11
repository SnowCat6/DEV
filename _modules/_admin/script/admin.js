// JavaScript Document

$(function()
{
	$($(".adminToolMenu .adminTabContent")
		.hide().get(0)).show();
	
	$(".adminToolMenu .adminTabSelector")
	.click(function(){
		return false;
	})
	.hover(function()
	{
		var thisElm = $(".adminToolMenu .adminTabContent" + $(this).attr("href"));
		$(".adminToolMenu .adminTabContent").not(thisElm).hide();
		thisElm.show();
		
		$(".adminToolMenu .adminTabSelector").removeClass("current");
		$(this).addClass("current");
	});
});