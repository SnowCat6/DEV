// JavaScript Document

var imageDropTimer = 0;
$(function(){
	$(document).on("jqReady ready", function()
	{
		$(".editorImageHolder .image a")
		.unbind("click.imageUpload")
		.on("click.imageUpload", function()
		{
			var size = $(this).parent().parent().find(".size span").text().split(" x ");
			var html = '<img src="' + $(this).attr("href") + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';
			editorInsertHTML(null, html);
			return false;
		});
		
		$("body, .editorImages")
		.unbind("dragover.imageUpload dragleave.imageUpload")
		.on("dragover.imageUpload", function(event)
		{
			clearTimeout(imageDropTimer);
			imageDropTimer = 0;
			$(".editorImages").addClass('hover');
			return false;
		})
		.on("dragleave.imageUpload", function(event)
		{
			var target = $(this);
			if (!target.hasClass("editorImages") &&
				target.get(0).tagName != 'BODY'){
				return;
			}
				
			imageDropTimer = setTimeout(function(){
				imageDropTimer = 0;
				$(".editorImages").removeClass('hover');
			}, 100);
				
			event.dropEffect = "none";
			return false;
		});
		
		$(".editorImages")
		.on("mouseleave", function(){
			$(".editorImages").removeClass('hover');
		});
		
		$(".editorImageHolder .size a")
		.unbind("click.imageUpload")
		.on("click.imageUpload", function()
		{
			$(this).parent().parent().addClass("delete")
			.fileDelete($(this).attr("rel"), function(responce)
			{
				var result = responce['result'];
				if (result['error']){
					alert(result['error']);
					return;
				}
				
				var p = $(this).parent();
				$(this).remove();
				if (p.find("tr").length > 1) return;
				$('<tr><td colspan="2" class="noImage">Нет изображений</td></tr>').appendTo(p);
			});
			return false;
		});
		
		$(".editorImageReload")
		.unbind("click.imageUpload")
		.on("click.imageUpload", function()
		{
			var r = $(this).parent();
			r.html('<div class="editorImageReload reload"><span />Обновление...');
			r.load('file_images.htm?' + $(this).attr("rel"), function(html){
				r.replaceWith(html);
				$(document).trigger("jqReady");
			});
			return false;
		});
		
		$(".editorImageUpload")
		.fileUpload(function(responce)
		{
			var img2insert = '';
			var holder = $(this).parent().parent().parent();
			for(var image in responce)
			{
				var prop = responce[image];
				if (prop['error']){
					alert(prop['error']);
					continue;
				}
				var dimension = prop['dimension'];
				var path = prop['path'];
				
				holder.find("a:contains('"+image+"')").parent().parent().remove();
				if (image.indexOf("/Image/")){
					var size = dimension.split(' x ');
					img2insert += '<img src="' + path + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';
				}
	
				var html = '<tr>';
				html += '<td class="image"><a href="' + path + '" target="_blank">'+image+'</a></td>';
				html += '<td class="size"><a href="#" rel="' + path + '"><span>'+dimension+'</span><del>удалить</del><b>вставить</b></a></td>';
				html += '</tr>';
				holder.append(html);
			}
			if (img2insert) editorInsertHTML(null, img2insert);
			if (html) $(holder.find(".noImage")).parent().remove();
			$(document).trigger('jqReady');
		});
	});
});

