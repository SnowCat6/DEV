<? function script_draggable(){
@define('noCache', true);
module('script:jq_ui')?>

<script language="javascript" type="text/javascript">
var dropped = false;
$(function(){
	bindDraggable();
	$("[rel*=droppable]").droppable({
		hoverClass: "ui-state-active",
		tolerance: "pointer",
		drop: function(event, ui )
		{
			dropped = true;
			var rel = ui.draggable.attr("rel");
			if ($(this).find("[rel=" + rel + "]").size()) return;
			itemStateChanged(rel, $(this), true);
		}
    });
});
function bindDraggable()
{
	$("[rel*=draggable]").draggable({
		appendTo: "body", cursor: "move",
		helper: function(){
			var r = $("<div />").css({
				"background": "white",
				"z-index": 999
			});
			if ($(this).hasClass("adminEditMenu")){
				var p = $(this).parent(".adminEditArea");
				p.clone().appendTo(r);
				r.css({
					"width": p.width(),
					"height": p.height()
					}).appendTo(p);
			}else{
				$(this).clone().appendTo(r);
				r.css({
					"color": "white",
					"padding": 10
					});
			}
			return r;
		},
		start: function()
			{
				dropped = false;
				$("[rel*=droppable]").addClass("dragStart");
				$("#fadeOverlayLayer,#fadeOverlayHolder").hide();
			},
		stop: function(e , ui){
			$("[rel*=droppable]").removeClass("dragStart");
			$("#fadeOverlayLayer,#fadeOverlayHolder").show();
			if (dropped) return;
			dropped = true;
			itemStateChanged($(this).attr("rel"), $(this).parents('[rel*=droppable]'),false);
		}
	});
}
function itemStateChanged(id, holders, bAdded)
{
	if (!holders.length) return;
	var holder = $(holders.get(0));
	
	id = id.split("-");
	rel = holder.attr("rel").split(":");
	if (!bAdded && !confirm("Удплить из списка?")) return;

	switch(id[1]){
		case "doc":
			var url = id[2] + ".htm?ajax=" + (bAdded?'itemAdd':'itemRemove') + "&" + rel[1];
			$.ajax(url)
			.success(function(data){
				holder.html(data); bindDraggable();
				$(document).trigger("jqReady");
			});
		break;
	}
}
</script>
<? } ?>
