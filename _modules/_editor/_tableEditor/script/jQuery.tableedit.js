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
				$(this).children().hide();
				tableEditorInit($(this).find(".inlineTableData"));
			});
			
		return false;
	});
}

function tableEditorSubmit(thisElm)
{
	try{
		var cfg = $.parseJSON(thisElm.attr("rel"));
		var action = cfg["action"];
	}catch(e){ };

	var ctx = thisElm.closest(".inlineTableEditor");
	$.get(action, {
		"document": thisElm.val()
	}, function(data){
		ctx.closest(".adminEditArea").replaceWith(data);
		tableInit();
	});
}

function tableEditorInit(thisElm)
{
	var id = 'jqTable-' + tableUniqueId++;
	thisElm.hide().after("<div id='" + id + "'></div>");
	
	var rows = 1;
	var cols = 1;
	try{
		var cfg = $.parseJSON(thisElm.attr("rel"));
		rows = cfg["rows"];
		cols = cfg["cols"];
	}catch(e){ };
	
	var data = getTableData(thisElm);
	var h = thisElm.attr("rows") * 30;

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