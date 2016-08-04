// JavaScript Document

$(function(){
	$(".imageTitleUpload")
	.fileUpload(function(responce)
	{
		for(var image in responce)
		{
			var attr = responce[image];
			if (attr['error']){
				alert(attr['error']);
				continue;
			}
			
			var fileName = attr['path'];
			$(".imageTitleHolderImage").html('<img src="' + fileName + '" />');
			$(".imageTitleName span").text(fileName);
			$("#imageTitleHolder").attr("class", "imageTitleLoaded");
			break;
		}
	});
	$(".imageTitleDelete").click(function()
	{
		var fileName = $(this).parent().parent().find(".imageTitleName span").text();
		$(this).fileDelete(fileName, function(responce)
		{
			var result = responce['result'];
			if (result['error']){
				alert(result['error']);
				return;
			}
			$(".imageTitleHolderImage").html('');
			$("#imageTitleHolder").attr("class", "imageTitleNotLoaded");
		});
		return false;
	});
});

$(function(){
	$(".imageUploadFull").fileUpload(function(responce)
	{
		var holder = $($(this).parents(".imageUploadFullHolder").find(".imageUploadFullTable"));
		for(var image in responce)
		{
			var prop = responce[image];
			if (prop['error']) continue;
			holder.find("a:contains('"+image+"')").parent().parent().remove();

			var size = Math.round(prop['size'] / 1024, 2);
			var date = prop['date'];
			var html = '<tr>';
			html += '<td class="delete"><a href="#" rel="'+prop['path']+'">x</a></td>';
			html += '<td><a href="'+prop['path']+'" target="_new">'+image+'</a></td>';
			html += '<td nowrap="nowrap">'+size+'Кб.</td>';
			html += '<td nowrap="nowrap">'+date+'</td>';
			html += '</tr>';
			holder.append(html);
		}
		$(document).trigger('jqReady');
	});
	$(document).on('ready jqReady', function()
	{
		$(".imageUploadFullTable td.delete a").on('click.delete',function(){
			$(this).parent().parent().addClass("delete")
			.fileDelete($(this).attr("rel"), function(){
				$(this).remove();
			});
			return false;
		});
	});
});
