// JavaScript Document

$(function(){
	$(".adminAccardion").accordion();
	widgetItemSortHandle();
	widgetItemReplaceHandle();
});
function updateWidget(widgetID)
{
	$.get('admin_widgetLoad.htm', {
		widgetID: widgetID
	}).done(function(data){
		$(".adminWidget#" + widgetID).html(data);
		$(document).trigger("jqReady");
		widgetItemSortHandle();
	});
}
function widgetItemReplaceHandle()
{
	$(".adminWidgetReplace a")
	.unbind()
	.click(function()
	{
		$().overlay("message", "Обновление данных ...");
		var rel = $.parseJSON($(this).attr("rel"));
		var url	= $(this).closest('form').attr("action");
		$.get(url, {
			adminWidgetReplace: rel['widgetType']
		}).done(function(data)
		{
			var queryString = {};
			url.replace(
				new RegExp("([^?=&]+)(=([^&]*))?", "g"),
				function($0, $1, $2, $3) { queryString[$1] = $3; }
			);
			var widgetID	= queryString['widgetID'];
			$(".adminWidget#" + widgetID).html(data);
			$(document).trigger("jqReady");
			widgetItemSortHandle();
			$().overlay("close");
		});

		return false;
	});
}
function widgetItemSortHandle()
{
	$(".adminHolderMenu .adminWidgetMenu .admin_sort_handle")
	.unbind()
	.mousedown(function()
	{
		$(this).closest(".adminHolderWidgets")
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
	
				var holderName	= holder.parent().attr("rel");
				$().overlay("message", "Обновление данных ...");
				$.get("admin_widgetLoad.htm", {
					"ids": ids,
					"holderName": holderName
				})
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