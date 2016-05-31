// JavaScript Document

var dropped = false;
var dropStack = new Array();
$(function(){
	$(document).on("ready jqReady", function()
	{
		$("[rel*=drag_data]")
		.uniqueId()
		.draggable({
			appendTo: "body",
			cursor: "move",
			helper: function()
			{
				var r = $('<div class="admin_droppable_helper" />');
				var p = $(this).closest(".adminEditMenu");
				if (p.length)
				{
					p = p.parent();
					p.clone().appendTo(r);
					r.css({
							"width": p.width(),
							"height":p.height()
						});
				}else{
					$(this).clone().appendTo(r);
					r.width($(this).width()).addClass("admin_droppable_holder");
					r.find("> ul").remove();
				}
				return r;
			},
			start: function()
			{
				dropped = false;
				var dragElm = $(this);
				
				try{
					var drag_data 	= $.parseJSON(dragElm.attr("rel"));
					var drag_type 	= drag_data['drag_data']['drag_type'];
					var bHideOverlay= !drag_data['drag_data']['overlay'];
				}catch(e){
					return;
				}
				
				if (bHideOverlay) $().overlay('hide');
				
				$(".admin_droppable")
				.each(function()
				{
					var thisElm = $(this);
					try{
						var drop_data = $.parseJSON($(this).attr("rel"));
						var drop_type = drop_data['drop_data'][':accept'];
					}catch(e){
						return;
					}
					var bAccept = false;
					for (var type in drop_type)
					{
						if (drag_type.indexOf(drop_type[type]) < 0) continue;
						bAccept = true;
						break;
					}
					if (!bAccept) return;
					if (thisElm.find('#' + dragElm.attr("id")).size())
						thisElm.addClass("ui-nondroppable")
					
					thisElm.droppable(
					{
						hoverClass: "admin-ui-state-active",
						tolerance: "pointer",
						drop: function(event, ui )
						{
							if (dropStack.length == 0)
								return;

							dropped = true;
							var elm = dropStack[dropStack.length-1];
							dropStack = new Array();
							itemStateChanged(ui.draggable, elm, true);
						},
						over: function(){
							if ($(this).hasClass("ui-nondroppable")) return;
							dropStack[dropStack.length] = thisElm;
						},
						out: function(){
							var ix = dropStack.indexOf(thisElm);
							if (ix >= 0){
								dropStack.splice(ix, 1);
							}
						}
					});
				});
			},
			stop: function(e , ui)
			{
				if (!dropped){
					dropped = true;
					itemStateChanged($(this), $(this).closest('.admin_droppable'),false);
				}
				$(".admin_droppable.ui-droppable, .admin_droppable.ui-nondroppable")
					.removeClass("ui-nondroppable")
					.droppable('destroy')
					.overlay("show");
			}
		});
	
		$('.admin_sort_handle').mousedown(function()
		{
			var holder = $(this).closest('.admin_sortable');
			if (holder.length) {
				itemSortHandle($(holder[0]));
			}
		}).css("cursor", "move");
	});
});
function itemSortHandle(holder)
{
		var sort_data, drop_data;
		try{
			drop_data = $.parseJSON(holder.attr("rel"));
			sort_data = drop_data['sort_data'];
			if (sort_data == null || drop_data == null) return;
		}catch(e){
			return;
		}

		var opts = {
			axis: sort_data['axis'],
			update: function()
			{
				drop_data['sort_data'] = new Array();
				if (!sort_data['itemFilter'])
					sort_data['itemFilter'] = '.admin_sort_handle';
				if (!sort_data['itemData'])
					sort_data['itemData'] = 'id';
				
				$(this).find(sort_data['itemFilter'])
				.each(function(){
					var d = $(this).attr(sort_data['itemData']);
					if (d) drop_data['sort_data'].push(d);
				});
				
				var bOverlay	= $("#fadeOverlayHolder").length > 0;
				if (!bOverlay) $().overlay("message", "Обновление данных ...");
				
				$.get(sort_data['action'], drop_data)
				.done(function(data)
				{
					if (!bOverlay) $().overlay("close");
					holder.html(data);
					$(document).trigger("jqReady");
				});
			}
		};
		
		var s = sort_data['select']?holder.find(sort_data['select']):holder;
		s.css("cursor", "move")
		s.sortable(opts).disableSelection();
}
function itemStateChanged(dragItem, holder, bAdded)
{
	try{
		var drop_data	= $.parseJSON(holder.attr("rel"));
		var drag_data	= $.parseJSON(dragItem.attr("rel"));
		var action		= bAdded?drag_data['drag_data']['actionAdd']:drag_data['drag_data']['actionRemove'];
		if (!action) return;
	}catch(e){
		return;
	}
	if (bAdded)
		return itemDropAction(holder, action, drop_data);

	$("#dialog-confirm").remove();
	$("<div id='dialog-confirm'>Удалить из списка?</div>").appendTo("body");
	$("#dialog-confirm").dialog({
		resizable: false, modal: true,
		title: "Удалить элемент из списка?",
		width: 400,
		buttons: {
			"Удалить": function ()
			{
				itemDropAction(holder, action, drop_data);
				$(this).dialog('close');
			},
			"Отменить": function ()
			{
				$(this).dialog('close');
			}
		}
	});
}

function itemDropAction(holder, action, drop_data)
{
	$.get(action, drop_data)
		.done(function(data)
		{
			holder.html(data);
			$(document).trigger("jqReady");
		});
}
