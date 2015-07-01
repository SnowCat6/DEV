$(function(){
	$("a[href*='?delete']")
	.click(function()
	{
		var url = $(this).attr("href") + 'Yes';
		$("<div id='dialog-confirm'>Удалить доумент?</div>").appendTo("body");
		$("#dialog-confirm" ).dialog({
			title: "Удалить документ?",
			resizable: false,
			height:200,
			width: 600,
			modal: true,
			buttons: {
				"Удалить": function() {
					$(this).dialog("close");
					ajaxLoad(url, 'ajax');
				},
			Cancel: function() {
				$(this).dialog("close");
				}
			}
		});
  		return false;
	});
});
