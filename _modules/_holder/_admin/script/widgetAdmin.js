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
			widgetItemSortHandle();
		});
	});
	widgetItemSortHandle();
});
function widgetItemSortHandle()
{
	$(".adminHolderMenu .adminWidgetMenu .admin_sort_handle")
	.mousedown(function()
	{
		$(this).closest(".adminHolderMenu")
		.sortable(
		{
			axis: "y",
			stop: function(){
				$(this).sortable("destroy");
			},
			update: function()
			{
				var holder = $(this);
				var ids = new Array();
				$(this).children(".adminWidget").map(function(){
					ids.push($(this).attr("id"));
				});
	
				var holderName	= holder.attr("rel");
				$().overlay("message", "Обновление данных ...");
				$.ajax("admin_widgetLoad.htm?ids=" + ids + "&holderName=" + holderName)
				.done(function(data)
				{
					$().overlay("close");
					holder.html(data);
					$(document).trigger("jqReady");
					widgetItemSortHandle();
				});
			}
		});
	});
}