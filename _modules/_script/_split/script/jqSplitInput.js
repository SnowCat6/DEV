// JavaScript Document

$(function()
{
	$(document).on("ready jqReady", function()
	{
		$("input.splitInput")
		.focus(function()
		{
			var splitter = ";";
			var thisElm = $(this);
			var val = thisElm.val()
				.split(splitter)
				.join("\n");
			if (val) val += "\n";
			
			var w = Math.max(thisElm.width(), 200);
			var h =thisElm.height() * 5;
			var offset = thisElm.offset();
	
			$("body").append("<div id='splitWInputidnow'><textarea /></div>");
	
			var holder = $("#splitWInputidnow")
			.width(w).height(h)
			.css({
				position: 'absolute',
				top: offset.top, left: offset.left,
				"z-index": 10000
			});
			
			var edit = holder.find("textarea")
			.css({
				width: "100%",
			})
			.addClass("input")
			.attr("rows", 6)
			.val(val)
			.focus()
			.blur(function(){
				var val = $(this).val()
					.trim()
					.split("\n")
					.join(splitter);
				thisElm.val(val);
				holder.remove();
			});
		});
	});
});