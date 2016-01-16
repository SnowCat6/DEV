// JavaScript Document

var bImportMouseDown = false;
var bImportFirstSelector = false;
$(function()
{
	reloadEvent();
	$(".button")
	.each(function()
	{
		var timeout = parseInt($(this).attr('reload'));
		if (isNaN(timeout)) return;
		
		var text = $(this).attr('oldText');
		if (undefined == text)
		{
			text = $(this).val();
			$(this).attr('oldText', text);
		}

		$(this).click(function()
		{
			$(this).val(text + " - обновление");
			$(this).attr('reload', "0");
		});
	});
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

function reloadEvent()
{
	$(".button")
	.each(function()
	{
		var timeout = parseInt($(this).attr('reload'));
		if (isNaN(timeout) || timeout == 0) return;

		var text = $(this).attr('oldText');
		if (undefined == text)
		{
			text = $(this).val();
			$(this).attr('oldText', text);
		}

		timeout -= 1;
		$(this).attr('reload', timeout);
		$(this).val(text + " - " + timeout + "сек.");		
		if (timeout > 0) return;

		$(this).val(text + " - обновление");		
		$(this).click();
	});

	setTimeout(reloadEvent, 1000);
}