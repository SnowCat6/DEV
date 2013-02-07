<? function script_draggable(){ ?>
<? module('script:jq_ui')?>
<script language="javascript" type="text/javascript">
var dropped = false;
$(function(){
	bindDraggable();
	$(".droppable").droppable({
		hoverClass: "ui-state-active",
		tolerance: "pointer",
		drop: function(event, ui )
		{
			dropped = true;
			var id = ui.draggable.attr("id");
			if ($(this).find("#" + id).size()) return;
			itemStateChanged(id, $(this), true);
		}
    });
});
function itemStateChanged(id, holder, bAdded)
{
	id = id.split("-");

	switch(id[1]){
		case "doc":
			$.ajax(id[2] + ".htm?ajax=" + (bAdded?'itemAdd':'itemRemove') + "&" + holder.attr("rel"))
			.success(function(data){
				holder.html(data);
				bindDraggable();
			});
		break;
	}
}
function bindDraggable(){
	$(".draggable" ).draggable({
		appendTo: "body",
		cursor: "move",
		helper: "clone",
		start: function()	{ dropped = false; $(".droppable").addClass("dragStart"); $("#fadeOverlayLayer,#fadeOverlayHolder").hide(); },
		stop: function(e , ui)	{
			$(".droppable").removeClass("dragStart"); $("#fadeOverlayLayer,#fadeOverlayHolder").show();
			if (dropped) return;
			itemStateChanged($(this).attr("id"), $(this).parents('.droppable'),false);
		}
	});
}
</script>
<? } ?>