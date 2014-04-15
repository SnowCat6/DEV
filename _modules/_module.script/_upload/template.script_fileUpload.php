<? function script_fileUpload(){ m('script:jq'); ?>
<script>
//	fileUpload
$(function(){
	$('body')
		.append('<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>')
		.append('<form action="{{url:file_images_upload}}" method="post" target="imageUploadFrame" id="imageUploadForm" enctype="multipart/form-data" style="display:none"></form>');
});

//	Upload files to folder
(function( $ )
{
	$.fn.fileUpload = function(options, callback)
	{
		var ev = null;
		if (typeof(options) == 'function'){
			ev = options;
		}else
		if (typeof(options) == 'string'){
			$(this).attr("rel", options);
		}
		if (typeof(callback) == 'function'){
			ev = callback;
		}

		return $(this)
		.css({'position': 'relative', 'overflow': 'hidden'})
		.each(function()
		{
			var thisElement = $(this);
			thisElement.find(".imageUploadField").remove();
			
			$('<input type="file" class="imageUploadField" name="imageFieldUpload[]" multiple />')
				.css({
					display: 'block', position: 'absolute',
					width: '100%', height: '100%',
					left: 0, top: 0,
					opacity: 0, filter:'alpha(opacity: 0)',
					cursor: 'pointer'
				})
				.appendTo($(this))
				.change(function(){
					$("#imageUploadFrame").unbind().load(function(){
						var responce = $(this).contents().find("body").html();
						if (ev) ev.call(thisElement, $.parseJSON(responce));
					});
					$("#imageUploadForm")
						.html('<input type="hidden" name="fileImagesPath" value="' + thisElement.attr("rel") + '" />')
						.append($(this)).submit();
					$(this).appendTo(thisElement);
			});
		});
	};
})( jQuery );

//	Dekete file from server
(function( $ ){
	$.fn.fileDelete = function(fileName, callback)
	{
		return $(this).each(function()
		{
			var thisElement = $(this);
			$.ajax('{{url:file_images_delete}}?fileImagesPath=' + fileName)
			.done(function(responce, status, jqXHR){
				if (callback) callback.call(thisElement, $.parseJSON(responce));
			});
		});
	};
})( jQuery );
</script>
<? } ?>
