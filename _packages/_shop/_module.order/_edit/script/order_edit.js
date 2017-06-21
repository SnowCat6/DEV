// JavaScript Document

$(function()
{
	$(document).on("load jqReady", function()
	{
		$(".shop_orders a")
		.not(".handle").addClass("handle")
		.click(function()
		{
			$(".shop_orders .item").removeClass("current")
			$(this).closest(".item").addClass("current");

			var url = $(this).attr("href") + "?ajax=ajaxResult";
			$(".shop_order").html("").load(url, function()
			{
				$(document).trigger("jqReady");
			});
			return false;
		});
	});
});
