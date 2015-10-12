// JavaScript Document


$(function()
{
	$("div.adminTabs")
	.uniqueId()
	.tabs({
		beforeLoad: function(event, ui) {
			// if the target panel is empty, return true
			return ui.panel.html() == "";
		},
		load: function( xhr, status ) {
			$(document).trigger("jqReady");
		}
	});
});
