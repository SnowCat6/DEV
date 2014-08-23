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
	//	Upload files to folder
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
		//	Clip input element under UI element
		.css({'position': 'relative', 'overflow': 'hidden'})
		.each(function()
		{
			// if this element initialized, skip
			var thisElement = $(this);
			if (thisElement.find(".imageUploadField").length) return;
			//	Append input field under UI element
			$('<input type="file" class="imageUploadField" name="imageFieldUpload[]" multiple />')
			//	Fill all UI space with input element
			//	Make input transparency
				.css({
					display: 'block', position: 'absolute',
					width: '100%', height: '100%',
					left: 0, top: 0,
					opacity: 0, filter:'alpha(opacity: 0)',
					cursor: 'pointer'
				})
				.appendTo($(this))
				//	On change submit hidden form to hidden frame
				.change(function()
				{
					//	Add onLoad event
					$("#imageUploadFrame").unbind().load(function(){
						//	Callback with JSON data
						var responce = $(this).contents().find("body").html();
						if (ev) ev.call(thisElement, $.parseJSON(responce));
					});
					//	Temporary move input to hidden form
					$("#imageUploadForm")
						.html('<input type="hidden" name="fileImagesPath" value="' + thisElement.attr("rel") + '" />')
						.append($(this)).submit();
					//	After submin move input back
					$(this).appendTo(thisElement);
			});
		});
	};
	
	//	Delete file from server
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
