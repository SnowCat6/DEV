
$(function()
{
	$(".widgetDocSwitchMenu a").hover(function()
	{
		var ix = $(this).attr("index");
		var sw = $(this).closest(".widgetDocSwitch").find(".CrossFadeEx");
		sw.CrossFadeEx("select", ix).CrossFadeEx("stop");
	}, function()
	{
		var sw = $(this).closest(".widgetDocSwitch").find(".CrossFadeEx");
		sw.CrossFadeEx("start");
	});
	$(".widgetDocSwitch .CrossFadeEx").CrossFadeEx(
	{
		callback: function(ix)
		{
			$(this).closest(".widgetDocSwitch").find(".widgetDocSwitchMenu .current").removeClass("current");
			$(this).closest(".widgetDocSwitch").find(".widgetDocSwitchMenu a[index=" + ix + "]")
				.addClass("current");
		}
	});
});