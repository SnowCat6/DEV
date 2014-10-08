// JavaScript Document
$(function(){
	$(".adminFileManage").fileUploadAdmin();
});

(function( $ )
{
	//	Upload files to folder
	$.fn.fileUploadAdmin = function(options)
	{
		$(this).click(function()
		{
			try{
				var cfg = $.parseJSON($(this).attr("rel"));
				var folder = cfg['uploadFolder'];
				var clip = folder.split('/');
				clip = clip.splice(0, clip.length-1)
				folder = clip.join('/');
			}catch(e){
				return false;
			}
			
			var ui = $("<div class='ajaxLoading'></div>")
			.overlay();

			$.ajax("file_images_get.htm?fileImagesPath=" + folder)
				.done(function(data)
			{
				$(".ajaxLoading").removeClass("ajaxLoading");
				
				try{
					var responce = $.parseJSON(data);
				}catch(e){ return; };
				
				_create(ui, responce, folder);
			});
			
			return false;
		});
		
		function _create(ui, files, folder)
		{
			var html = '<h1>' +  folder +'</h1>';
			html += '<a href="#" class="ajaxClose">X</a>';
			
			html += '<div class="content">';
			html += '<form action="file_images_delete.htm">';
			for(var file in files)
			{
				var f = files[file];
				html += '<h2>' + file + '</h2>';
				for(var f2 in f){
					var f3 = folder + '/' + file + '/' + f2;
					html += '<div><label><input type="checkbox" name="delete[]" value="' + f3 + '" />' + f2 + '</label></div>';
				}
			};
			
			html += '<p><input type="submit" value="Удалить" class="button"></p>';
			html += '</form>';
			html += '</div>';
			
			ui.addClass('fileUploadAdmin')
			.html(html);
			
			$(".ajaxClose").click(function(){
				$().overlay("close");
				return false;
			});
			ui.find("form").ajaxForm(function(){
				document.location.reload();
			});
		}
	}
})( jQuery );

