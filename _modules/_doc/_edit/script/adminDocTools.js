// JavaScript Document
$(function()
{
	$(".adminDocToolsHolder h2 a")
	.on("mouseover click", function(){
		var id = $(this).attr("href");
		$(".adminDocToolsHolder .selected").removeClass("selected");
		$(id).add($(this).parent()).addClass("selected");
	});
});