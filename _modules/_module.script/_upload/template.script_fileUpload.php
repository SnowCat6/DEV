<? function script_fileUpload(){ m('script:jq'); ?>
<script>
//	fileUpload
$(function(){
	$('body')
		.append('<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>')
		.append('<form action="{{url:file_images_upload}}" method="post" target="imageUploadFrame" id="imageUploadForm" enctype="multipart/form-data" style="display:none"></form>');
});
(function( $ )
{
	$.fn.fileUpload = function(options, event)
	{
		var ev = null;
		if (typeof(options) == 'function'){
			ev = options;
//			$(this).on('fileUploaded', options);
		}else
		if (typeof(options) == 'string'){
			$(this).attr("rel", options);
		}
		if (typeof(event) == 'function'){
			ev = event;
//			$(this).on('fileUploaded', event);
		}

		return $(this)
		.css({'position': 'relative', 'overflow': 'hidden'})
		.each(function()
		{
			var thisElement = $(this);
			thisElement.find(".imageUploadField").remove();
			thisElement.unbind("fileUploaded");
			if (ev) thisElement.on('fileUploaded', ev);
			
			$('<input type="file" class="imageUploadField" name="imageFieldUpload[]" multiple />')
				.css({
					display: 'block', position: 'absolute',
					width: '100%', height: '100%',
					left: 0, top: 0,
					opacity: 0, filter:'alpha(opacity: 0)',
					cursor: 'pointer'
				})
				.attr("rel", $(this).attr("rel"))
				.appendTo($(this))
				.change(function(){
					$("#imageUploadFrame").unbind().load(function(){
						var responce = $(this).contents().find("body").html();
						thisElement.trigger("fileUploaded", $.parseJSON(responce));
					});
					$("#imageUploadForm")
						.html('<input type="hidden" name="fileImagesPath" value="' + $(this).attr("rel") + '" />')
						.append($(this)).submit();
					$(this).appendTo(thisElement);
			});
		});
	};
})( jQuery );

//	fileDelete
(function( $ ){
	$.fn.fileDelete = function(fileName, event)
	{
		if (event){
			$(this).unbind('fileDeleted');
			$(this).on('fileDeleted', event);
		}
		
		return $(this)
		.each(function(){
			var thisElement = $(this);
			$.ajax('{{url:file_images_delete}}?fileImagesPath=' + fileName)
			.done(function(responce, status, jqXHR){
				thisElement.trigger("fileDeleted", $.parseJSON(responce));
			});
		});
	};
})( jQuery );
</script>
<? } ?>
