<? function script_draggable(){ ?>
<? module('script:jq_ui')?>
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
function itemStateChanged(id, holder, bAdded)
{
	id = id.split("-");
	rel = holder.attr("rel").split(":");

	switch(id[1]){
		case "doc":
			var url = id[2] + ".htm?ajax=" + (bAdded?'itemAdd':'itemRemove') + "&" + rel[1];
			$.ajax(url)
			.success(function(data){
				holder.html(data);
				bindDraggable();
			});
		break;
	}
}
function bindDraggable(){
	$("[rel*=draggable]" ).draggable({
		appendTo: "body",
		cursor: "move",
		helper: "clone",
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
			itemStateChanged($(this).attr("rel"), $(this).parents('[rel*=droppable]'),false);
		}
	});
}
</script>
<? } ?>