// JavaScript Document

$(function(){
	$("input[name=htaccessOverride]")
	.change(function()
	{
		if ($(this).is(":checked"))
		{
			$("#globalSettingsHtaccess")
			.prop("disabled", false)
			.css({
				background: "red",
				color: "white"
			});
		}else{
			$("#globalSettingsHtaccess")
			.prop('disabled', 'disabled')
			.css({
				background: "",
				color: ""
			});
		}
	});
});


$(function(){
	$(".globalSiteRules tbody.sortRules").sortable({
		axis: "y"
	});
	$(".copy2rule")
	.click(function(){
		var v = $(this).parent().parent().find("td");
		var val = $(v.get(1)).text();
		$(v.get(3)).find("input").val(val);
		return false;
	});
});
