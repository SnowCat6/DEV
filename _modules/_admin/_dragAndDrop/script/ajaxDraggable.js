// JavaScript Document

var dropped = false;
$(function(){
	bindDraggable();
});
function bindDraggable()
{
	$("[rel*=drag_data]")
	.uniqueId()
	.draggable({
		appendTo: "body",
		cursor: "move",
		helper: function(){
			var r = $('<div />').css({
				"background": "white",
				"z-index": 999
			});
			var p = $(this).parent(".adminEditMenu");
			if (p.length){
				p = p.parent();
				p.clone().appendTo(r);
				r.css({"width": p.width(), "height": p.height()}).appendTo(p);
			}else{
				$(this).clone().appendTo(r);
				r.css({ "color": "white", "padding": 10, width: $(this).width() });
				r.find("> ul").remove();
			}
			return r;
		},
		start: function()
			{
				dropped = false;
				$(".admin_droppable").droppable(
				{
					hoverClass: "ui-state-active",
					tolerance: "pointer",
					drop: function(event, ui )
					{
						dropped = true;
						if ($(this).find("#" + $(this).attr("id")).size()) return;
						itemStateChanged(ui.draggable, $(this), true);
					}
				})
				.addClass("dragStart")
				.overlay('hide');;
			},
		stop: function(e , ui)
		{
			$(".admin_droppable").
				droppable('destroy')
				.removeClass("dragStart")
				.overlay("show");
				
			if (dropped) return;
			dropped = true;
			itemStateChanged($(this), $(this).parents('.admin_droppable'),false);
		}
	});
	$(".sortable").sortable().disableSelection();
}
function itemStateChanged(dragItem, holders, bAdded)
{
	if (!holders.length) return;
	var holder = $(holders.get(0));
	
	try{
		var drop_data	= $.parseJSON(holder.attr("rel"));
		var drag_data	= $.parseJSON(dragItem.attr("rel"));
		var action		= bAdded?drag_data['drag_data']['actionAdd']:drag_data['drag_data']['actionRemove'];
		if (!action) return;
	}catch(e){
		return;
	}
	
	if (!bAdded && !confirm("Удплить из списка?")) return;

	$.ajax(action + "&" + $.param(drop_data['drop_data']))
		.success(function(data)
		{
			holder.html(data);
			bindDraggable();
			$(document).trigger("jqReady");
		});
}

