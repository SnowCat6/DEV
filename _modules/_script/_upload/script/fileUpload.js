// JavaScript Document

//	fileUpload
(function( $ )
{
	//	Upload files to folder
	$.fn.fileUpload = function(options, callback)
	{
		var opts = $.extend( {}, $.fn.fileUpload.defaults, options );

		if (typeof(options) == 'function'){
			opts.callback = options;
		}else
		if (typeof(options) == 'string'){
			opts.uploadFolder	= options;
		}
		if (typeof(callback) == 'function'){
			opts.callback = callback;
		}

		return $(this)
		//	Clip input element under UI element
		.css({'position': 'relative', 'overflow': 'hidden'})
		.each(function()
		{
			// if this element initialized, skip
			var thisElement = $(this);
			if (thisElement.find(".imageUploadField").length) return;
			//	Upload file folder
			var uploadFolder = thisElement.attr("rel")?thisElement.attr("rel"):opts.uploadFolder;
			if (uploadFolder == "") return;
			//	Append input field under UI element
			$('<input type="file" class="imageUploadField" name="imageFieldUpload[]" multiple />')
				//	Input styling
				.css(opts.cssInput)
				.appendTo(thisElement)
				//	On change submit hidden form to hidden frame
				.change(function()
				{
					if ($("#imageUploadFrame").length == 0)	$('body')
						.append('<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>')
					if ($("#imageUploadForm").length == 0)	$('body')
						.append('<form action="file_images_upload.htm" method="post" target="imageUploadFrame" id="imageUploadForm" enctype="multipart/form-data" style="display:none"></form>');
					//	Add onLoad event
					$("#imageUploadFrame").unbind().load(function(){
						//	Callback with JSON data
						var responce = $(this).contents().find("body").html();
						opts.callback.call(thisElement, $.parseJSON(responce));
					});
					//	Temporary move input to hidden form
					$("#imageUploadForm")
						.html('<input type="hidden" name="fileImagesPath" value="' + uploadFolder + '" />')
						.append($(this)).submit();
					//	After submin move input back
					$(this).appendTo(thisElement);
			});
		});
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.fileUpload.defaults = 
	{
		uploadFolder:	"",
		callback:	function() {},
		cssInput:	{
			display: 	'block', position: 'absolute',
			width: 		'100%', height: '100%',
			left:		0, top: 0,
			opacity: 	0, filter:'alpha(opacity: 0)',
			cursor:		'pointer'
		}
	};
	
	//	Delete file from server
	$.fn.fileDelete = function(fileName, options)
	{
		var opts = $.extend( {}, $.fn.fileDelete.defaults, options );
		if (typeof(options) == 'function'){
			opts.callback = options;
		}

		return $(this).each(function()
		{
			var thisElement = $(this);
			$.ajax('file_images_delete.htm?fileImagesPath=' + fileName)
			.done(function(responce){
				opts.callback.call(thisElement, $.parseJSON(responce));
			});
		});
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.fileDelete.defaults = 
	{
		callback:	function() {}
	};
})( jQuery );
