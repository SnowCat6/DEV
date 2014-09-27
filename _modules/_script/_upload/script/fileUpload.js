// JavaScript Document

//	fileUpload
(function( $ )
{
	//	Upload files to folder
	$.fn.fileUpload = function(method)
	{
		var methods = {
			init:	thisInit,		//	Init method
			show:	thisShow,		//	Show drag message
			hide:	thisHide,		//	Hide drag message
			"d&d":	thisDragInit,	//	Initialize Darg&Drop
		};
		var DragAndDropTimer = 0;
		var DragAndDropElm = null;
		
		return methods[method]?
			methods[method].apply(this, Array.prototype.slice.call(arguments, 1)):
			methods['init'].apply(this, Array.prototype.slice.call(arguments));
		
		//	Initialize object, add UI elements and input field
		function thisInit(options, callback)
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
			.css(opts.cssElm)
			.each(function()
			{
				// if this element initialized, skip
				var thisElement = $(this);
				_create(thisElement, opts);
			});
		};
		//	Show darg&drop message
		function thisShow(){
		}
		//	Hide darg&drop message
		function thisHide(){
		}
		//	Attach Drag&Drop events
		function thisDragInit(options)
		{
			var opts = $.extend( {}, $.fn.fileUpload.defaults, options);

			var thisElm = $(this);
			var thisBody = _getBody(thisElm);
			
			if (thisBody.hasClass("fileUploadDragAndDrop")) return;
			thisBody.addClass("fileUploadDragAndDrop");

			$("body").add(thisBody.parents("html"))
			.on({
				"dragover.fileUpload":	function(event){
					_thisShow(thisElm, opts);
				},
				"dragleave.fileUpload":	function(event){
					_thisHide(thisElm);
				},
				"drop.fileUpload":		function(event){
					_thisHide(thisElm);
				}
			});
		}
		function _getBody(thisElm){
			return thisElm.is("body")?thisElm:thisElm.parents("body");
		}
		function _thisShow(thisElm, opts)
		{
			clearTimeout(DragAndDropTimer);
			DragAndDropTimer = 0;
			
			var thisBody = _getBody(thisElm);
			
			if (thisBody.hasClass("fileUploadDrag")) return false;
			thisBody.addClass("fileUploadDrag");
			
			opts.css	= $.extend( {}, $.fn.fileUpload.defaults.css, opts.cssDrag );
			_create(thisElm, opts);
			return true;
		}
		//	Hide darg&drop message
		function _thisHide(thisElm){
			DragAndDropElm = thisElm;
			clearTimeout(DragAndDropTimer);
			DragAndDropTimer = setTimeout(_thisHideTimeout, 50);
		}
		function _thisHideTimeout(){
			var thisBody = _getBody(DragAndDropElm);
			thisBody.removeClass("fileUploadDrag");
			_destroy(DragAndDropElm);
		}
		function _create(thisElement, opts)
		{
			if (thisElement.find(".imageUploadField").length) return;
			//	Upload file folder
			var uploadFolder = opts.uploadFolder;
			if (uploadFolder == ""){
				try{
					var cfg = $.parseJSON(thisElement.attr("rel"));
					uploadFolder = cfg['folder'];
				}catch(e){ }
			}
			if (uploadFolder == "") return;
			
			var thisBody = _getBody(thisElement);
			//	Add UI holder
			var ui = $('<div class="imageUploadField">')
				.css(opts.css)
				.appendTo(thisElement);
			//	Supports UI elements
			$('<div class="imageUploadContent">' + opts.content + '</div>').appendTo(ui);
			$('<div class="imageUploadMessage"></div>').appendTo(ui);
			//	Append input field under UI element
			$('<input type="file" name="imageFieldUpload[]" multiple />')
				//	Input styling
				.css(opts.cssInput)
				.attr("title", opts.message)
				.appendTo(ui)
				//	On change submit hidden form to hidden frame
				.change(function()
				{
					if (thisBody.find("#imageUploadFrame").length == 0)	thisBody
						.append('<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>')
					if (thisBody.find("#imageUploadForm").length == 0)	thisBody
						.append('<form action="file_images_upload.htm" method="post" target="imageUploadFrame" id="imageUploadForm" enctype="multipart/form-data" style="display:none"></form>');
					//	Add onLoad event
					thisBody.find("#imageUploadFrame").unbind().load(function(){
						//	Callback with JSON data
						var responce = $(this).contents().find("body").html();
						opts.callback.call(thisElement, $.parseJSON(responce));
					});
					//	Temporary move input to hidden form
					thisBody.find("#imageUploadForm")
						.html('<input type="hidden" name="' + opts.uploadField + '" value="' + uploadFolder + '" />')
						.append($(this))
						.submit();
					//	After submit move input field back
					$(this).appendTo(ui);
			});
		}
		function _destroy(thisElement){
			thisElement.find(".imageUploadField").remove();
		}
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.fileUpload.defaults = 
	{
		uploadFolder:	"",				// Server path to upload
		uploadField:	"fileImagesPath",
		callback:		function() {},	// Call after uploading
		//	Automatic drag&drop holder generation
		dragSupport:	false,	//	Support autocreate drag&drop golder
		dragStartMessage:	"Перетащите файлы сюда.",
		dragUploadMessage:	"Загрузка файлов, подождите.",
		//	Inner HTML code for UI element
		content:	"",
		message:	"Нажмите для загрузки файла",
		//	CSS style main UI element
		css:{
			display: 	'block', position: 'absolute', overflow: 'hidden',
			width: 		'100%', height: '100%',
			left:		0, top: 0, "z-index": 9999,
		},
		//	CSS style owner's object
		cssElm:{
			position: 'relative'
		},
		//	CSS style input hidden element
		cssInput:	{				// CSS style of input overlay field
			display: 	'block', position: 'absolute',
			width: 		'100%', height: '100%',
			opacity: 	0, filter:'alpha(opacity: 0)',
			cursor:		'pointer'
		},
		//	CSS style drag&drop place
		cssDrag:	{
			background: "red"
		},
		//	CSS style uploading message
		cssUpload:	{
			background: "green"
		},
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
