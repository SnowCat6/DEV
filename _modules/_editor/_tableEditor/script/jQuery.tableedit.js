// JavaScript Document

var tableUniqueId = 0;
$(function()
{
	$(".tableEditor").each(function(){
		tableEditorInit($(this));
	});
	tableInit();
});

function tableInit()
{
	$("a#inlineTableEditor")
	.removeAttr("id")
	.addClass("inlineTableMenu")
	.click(function()
	{
		var thisElm = $(this);
		var oldLabel = thisElm.attr("oldLabel");
		
		if (oldLabel)
		{
			thisElm.text(oldLabel);
			thisElm.removeAttr("oldLabel");
			
			$(this).closest(".adminEditArea")
				.find(".inlineTableEditor")
				.each(function(ndx){
					tableEditorSubmit($(this).find(".inlineTableData"));
				});
			return false;
		}
		
		thisElm.attr("oldLabel", thisElm.text());
		thisElm.text("Сохранить");
		
		$(this).closest(".adminEditArea")
			.find(".inlineTableEditor")
			.each(function(ndx){
				var h = $(this).height();
				$(this).children().hide();
				tableEditorInit($(this).find(".inlineTableData"), h);
			});
			
		return false;
	});
	$(".inlineTableEditor")
	.unbind()
	.dblclick(function(e){
		$(this).closest(".adminEditArea")
		.find(".inlineTableMenu").each(function(){
			if ($(this).attr("oldLabel")) return;
			$(this).click();
		});
		e.stopPropagation();
	});
}

function tableEditorSubmit(thisElm)
{
	try{
		var cfg = $.parseJSON(thisElm.attr("rel"));
		var action = cfg["action"];
	}catch(e){ };

	var ctx = thisElm.closest(".inlineTableEditor");
	$.post(action, {
		"document": thisElm.val()
	}, function(data){
		ctx.closest(".adminEditArea").replaceWith(data);
		tableInit();
	});
}

function tableEditorInit(thisElm, h)
{
	var id = 'jqTable-' + tableUniqueId++;
	thisElm.hide().after("<div id='" + id + "'></div>");
	
	var rows = 1;
	var cols = 2;
	try{
		var cfg = $.parseJSON(thisElm.attr("rel"));
		rows = cfg["rows"];
		cols = cfg["cols"];
	}catch(e){ };
	
	var data = getTableData(thisElm);
	if (!h) h = thisElm.height();
	if (h < 400) h = 400;

	$("#" + id)
	.handsontable({
		data: data,
		height: h,
		minCols : cols, minRows: rows,
		rowHeaders: true,
		colHeaders: true,
		minSpareRows: 1,
		manualColumnResize: true,
		stretchH: 'all',
		contextMenu: true,
		afterChange: function(change, source)
		{
			if (source === 'loadData') return; //don't save this change
			setTableData(thisElm, data);
			if (source === 'paste') ;
		}
	});
}

function getTableData(thisElm)
{
	var	data = Array();
	var rows = thisElm.val().split("\n");
	for(var row in rows){
		data[row] = rows[row].split("\t");
	};
	return data;
}
function setTableData(thisElm, data)
{
	var raw = '';
	for(var row in data)
	{
		if (data[row].join("").trim() == "") continue;
		if (raw) raw += "\r\n";
		raw += data[row].join("\t").trim("\t");
	}
	thisElm.val(raw);
}