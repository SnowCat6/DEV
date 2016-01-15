// JavaScript Document

var bImportMouseDown = false;
var bImportFirstSelector = false;
$(function()
{
/*
	$(".importSelectAll").click(function(){
		doChangeCheckValue = true;
		var bCheck = $(this).prop('checked')?true:false;
		$(".importCommit td input").prop("checked", bCheck);
		doChangeCheckValue = false;
	});
	
	$(".importCommit tr").mousedown(function(){
		bImportMouseDown = true;
		bImportFirstSelector = $(this).find("td input").prop('checked')?true:false;
	}).mouseup(function(){
		bImportMouseDown = false;
	}).mouseenter(function(){
		if (bImportMouseDown == false) return;
		$(this).find("td input").prop('checked', !bImportFirstSelector);
	}).mouseleave(function(){
		if (bImportMouseDown == false) return;
		$(this).find("td input").prop('checked', !bImportFirstSelector);
	});
*/
	$(".importCommit .name").click(function()
	{
		var ctx = $(this).parent().next("tr");
		ctx.toggleClass("importData");
		
		var id = ctx.attr("rel");
		ctx = $(ctx.find("td").get(1));
		if (ctx.html()) return false;
		
		ctx.html('---- loading ----');
		
		ctx.load("import_commit_get.htm", {
			ajax: "",
			id: id
		}, function(data)
		{
			$(this)
				.addClass("importRowInfo")
				.html(data)
				.find("td[rel*=importData]")
				.each(function()
				{
					var thisCell = $(this);
					$(this).closest("tr").find("td")
					.click(function()
					{
						if (thisCell.find("input").length == 0)
						{
							var html = '<input type="text" size="50" class="input" name="' + thisCell.attr("rel") + '" value="' + thisCell.text() +'" />';
							html = "<div>" + thisCell.html() + html + "</div>";
							thisCell.html(html);
						}
						thisCell.find("input")
							.show()
							.focus()
							.blur(function()
							{
								$(this).hide();
								thisCell.find("strong").text($(this).val());
								
								var data = $(this).serializeArray();
								$.get("import_commit_set.htm?ajax&id=" + id, data);
							});
					});
			});
			
			$(document).trigger("jqReady");
		});
	});
});
