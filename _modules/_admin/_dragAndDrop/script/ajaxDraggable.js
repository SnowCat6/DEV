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
				try{
					var drag_data = $.parseJSON($(this).attr("rel"));
					var drag_type = drag_data['drag_data']['drag_type'];
				}catch(e){
					return;
				}
				
				$(".admin_droppable")
				.overlay('hide')
				.each(function()
				{
					try{
						var drop_data = $.parseJSON($(this).attr("rel"));
						var drop_type = drop_data['drop_data']['drop_type'];
					}catch(e){
						return;
					}
					var bAccept = false;
					for (var type in drop_type){
						if (drag_type.indexOf(drop_type[type]) < 0) continue;
						bAccept = true;
						break;
					}
					if (!bAccept) return;
					
					$(this)
					.droppable(
					{
						hoverClass: "admin-ui-state-active",
						tolerance: "pointer",
						drop: function(event, ui )
						{
							dropped = true;
							if ($(this).find("#" + $(this).attr("id")).size()) return;
							itemStateChanged(ui.draggable, $(this), true);
						}
					})
				});
			},
		stop: function(e , ui)
		{
			if (!dropped){
				dropped = true;
				itemStateChanged($(this), $(this).parents('.admin_droppable'),false);
			}
			$(".admin_droppable.ui-droppable")
				.droppable('destroy')
				.overlay("show");
				
		}
	});

	$(".sortable").sortable().disableSelection();

	$('.admin_droppable').each(function()
	{
		try{
			var drop_data = $.parseJSON($(this).attr("rel"));
			var sort_data = drop_data['sort_data'];
			drop_data = drop_data['drop_data'];
			if (sort_data == null || drop_data == null) return;
		}catch(e){
			return;
		}

		var holder = $(this);
		var s = sort_data['select']?$(this).find(sort_data['select']):$(this);
		var opts = {
			axis: sort_data['axis'],
			update: function()
			{
				drop_data['sort_data'] = new Array();
				if (sort_data['itemFilter'])
				{
					$(this).find(sort_data['itemFilter'])
					.each(function(){
						var d = $(this).attr(sort_data['itemData']);
						if (d) drop_data['sort_data'].push(d);
					});
				}
				
				$.ajax(sort_data['action'] + '&' + $.param(drop_data))
				.done(function(data){
					holder.html(data);
					bindDraggable();
					$(document).trigger("jqReady");
				});
			}
		};
		
		s
		.sortable(opts)
		.css({ cursor: "move" });
	});
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

