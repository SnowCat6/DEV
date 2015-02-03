// JavaScript Document
$(function(){
	$(".adminUndo a.undo_action")
	.attr("title", "")
	.tooltip({
		content: function(){
			return "... ожидание ответа ...";
		},
		open: function(evt, ui) 
		{
			var elem = $(this);
			$.ajax(elem.attr("rel")).always(function(ctx) {
				if (ctx == "") ctx = "Нет информации";
				elem.tooltip('option', 'content', ctx);
			});
		}
	});
});