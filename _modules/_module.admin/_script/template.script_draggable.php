<? function script_draggable(){ ?>
<? module('script:jq_ui')?>
<script language="javascript" type="text/javascript">
$(function() {
	$(".draggable" ).draggable({
		appendTo: "body",
		cursor: "move",
		helper: "clone",
		start: function()	{ $(".droppable").addClass("dragStart"); $("#fadeOverlayLayer,#fadeOverlayHolder").hide(); },
		stop: function()	{ $(".droppable").removeClass("dragStart"); $("#fadeOverlayLayer,#fadeOverlayHolder").show(); }
	});
	$(".droppable").droppable({
		hoverClass: "ui-state-active",
		tolerance: "pointer",
		drop: function(event, ui )
		{
			var id = ui.draggable.attr("id");
			if ($(this).find("#" + id).size()) return;
			id = id.split("-");


			var holder = $(this);
			switch(id[1]){
				case "doc":
					$.ajax(id[2] + ".htm?ajax=reload&" + $(this).attr("rel"))
					.success(function(data){
						holder.html(data);
					});
				break;
			}
		}
    });
});
</script>
<? } ?>