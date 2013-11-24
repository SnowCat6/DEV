<? function script_fileUpload(){ m('script:jq'); ?>
<style>
.imageUploadForm, #imageUploadFrame{
	display:none;
}
.imageUploadField{
	display:block;
	position:absolute;
	width:100%; height:100%;
	left: 0; top: 0;
	opacity: 0; filter:alpha(opacity: 0);
	cursor:pointer;
}
</style>
<iframe name="imageUploadFrame" id="imageUploadFrame"></iframe>
<form action="{{url:file_images_upload}}" method="post" target="imageUploadFrame" class="imageUploadForm" enctype="multipart/form-data"></form>
<script>
//	fileUpload
(function( $ ) {
	$.fn.fileUpload = function(options, event)
	{
		if (typeof(options) == 'function'){
			$(this).on('fileUploaded', options);
		}else
		if (typeof(options) == 'string'){
			$(this).attr("rel", options);
		}
		if (typeof(event) == 'function'){
			$(this).on('fileUploaded', event);
		}

		return $(this)
		.css({'position': 'relative'})
		.each(function()
		{
			var thisElement = $(this);
			$('<input type="file" class="imageUploadField" name="imageFieldUpload[]" multiple />')
				.attr("rel", $(this).attr("rel"))
				.appendTo($(this))
				.change(function(){
					$("#imageUploadFrame").unbind().load(function(){
						var responce = $(this).contents().find("body").html();
						thisElement.trigger("fileUploaded", $.parseJSON(responce));
					});
					$(".imageUploadForm")
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
			$(this).on('fileDeleted', event);
		}
		
		return $(this)
		.each(function(){
			var thisElement = $(this);
			$.ajax('{{url:file_images_delete}}?fileImagesPath=' + fileName)
			.done(function(event, responce){
				thisElement.trigger("fileDeleted", responce);
			});
		});
	};
})( jQuery );
</script>
<? } ?>
