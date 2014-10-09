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
				var folder = _baseDir(cfg['uploadFolder']);
			}catch(e){
				return false;
			}
			
			var connector  = editorBaseFinder + "?Connector=file_fconnector2/" + folder + '.htm';
			
			var html = '<h1>' +  folder +'</h1>';
			html += '<a href="#" class="ajaxClose">X</a>';
			html += "<iframe src='" + connector + "'></iframe>";
			var ui = $("<div class='fileUploadAdmin'>" + html + "</div>")
			.overlay();

			$(".ajaxClose").click(function(){
				$().overlay("close");
				return false;
			});
			
			return false;
		});
		
		function _baseDir(path){
			var clip = path.split('/');
			clip = clip.splice(0, clip.length-1)
			return clip.join('/');
		}
	}
})( jQuery );

