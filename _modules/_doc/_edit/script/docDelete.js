$(function()
{
	$("a[href*='?delete']")
	.click(function()
	{
		var help = $(this).attr('rel');
		if (help) help = 'Вы хотите удалить <b>' + help + '</b>?';
		else help = 'Вы хотите удалить документ?';
		
		help +=  '<p>Действие можно отменить.</p>';
		
		var url = $(this).attr("href") + 'Yes';
		
		$("#dialog-confirm").remove();
		$("<div id='dialog-confirm'>" + help + "</div>").appendTo("body");
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
