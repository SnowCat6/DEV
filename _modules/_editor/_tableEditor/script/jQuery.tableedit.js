// JavaScript Document

var tableUniqueId = 0;
$(function()
{
//	for(a in Handsontable.ContextMenu) alert(a);
	
	$(".tableEditor").each(function()
	{
		var thisElm	= $(this);
		var id = 'jqTable-' + tableUniqueId++;
		$(this)
			.hide()
			.after("<div id='" + id + "'></div>");
		
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
	});
});

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
		raw += data[row].join("\t");
	}
	thisElm.val(raw);
}