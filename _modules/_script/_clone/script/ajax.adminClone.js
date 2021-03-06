// JavaScript Document

$(function(){
	$("input.adminReplicateButton").click(function(){
		return adminCloneByID($(this).attr('id'));
	}).removeClass("adminReplicateButton");
	
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
});
function adminCloneByID(id)
{
	var o = $(".adminReplicate#" + id);
	var o2 = o.clone()
		.insertBefore(o)
		.removeClass("adminReplicate");
	
	$(o2.find(".hasDatepicker"))
	.removeClass("hasDatepicker")
	.removeAttr("id")
	.uniqueId()
	.each(function(){
		attachDatetimepicker($(this));
	});
	
	$(o2.find(".autocomplete"))
	.each(function() {
		var o = $(this).attr("options");
		if (o) o = window[o];
		else o = null;
		
		$(this)
			.autocomplete(o)
			.on('focus', function(event) {
				aoutocompleteNow = $(this);
				$(this).autocomplete("search", "");
			});
    });
	
	$(".adminReplicate#" + id + " input").val("");
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
}
