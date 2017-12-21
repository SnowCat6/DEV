
var adminToolbarTimeout = 0;
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
	
	$(".adminTools a[id*=ajax]")
	.click(	function()
	{
		$(".adminTools").css("visibility", 'hidden');
		setTimeout(function()
		{
			$(".adminTools").css("visibility", '');
		});
	});
	$(".adminHover")
	.mouseenter(function()
	{
		$(".adminTools").css("visibility", 'visible');
		
		clearTimeout(adminToolbarTimeout);
		adminToolbarTimeout = setTimeout(function()
		{
			$(".adminTools").css("visibility", '');
		}, 1000);
	});
});

