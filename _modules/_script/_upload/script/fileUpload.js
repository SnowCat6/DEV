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
		
		return methods[method]?
			methods[method].apply(this, Array.prototype.slice.call(arguments, 1)):
			methods['init'].apply(this, Array.prototype.slice.call(arguments));
		
		//	Initialize object, add UI elements and input field
		function thisInit(options, callback)
		{
			var opts = _init(options, callback);
			
			return $(this)
			//	Clip input element under UI element
			.each(function()
			{
				// if this element initialized, skip
				_create($(this), opts);
			});
		};
		//	Show darg&drop message
		function thisShow(){
		}
		//	Hide darg&drop message
		function thisHide(){
		}
		//	Attach Drag&Drop events
		function thisDragInit(options, callback)
		{
			var opts = _init(options, callback);
			
			$(this).each(function()
			{
				if ($(this).hasClass("dragAndDropElement")) return false;
				
				$(this)
					.addClass("dragAndDropElement")
					.css(opts.cssDropHolder)
					.on({
						dragAndDropShow: function(){
							_thisShow($(this), opts);
						},
						dragAndDropHide: function(){
							_thisHideTimeout();
						},
						dragAndDropDrop: function()
						{
							$(".dragAndDropElement .imageUploadField")
							.not($(this).find(".imageUploadField"))
							.hide();
							
							_create($(this), opts)
								.css(opts.cssUpload)
								.find(".imageUploadMessage")
								.html(opts.dragUploadMessage);
						}
					});
			});
			
			var thisBody= _getBody($(this));
			if (thisBody.hasClass("fileUploadDragAndDrop")) return;
			thisBody.addClass("fileUploadDragAndDrop");

			thisBody.parents("html")
			.on({
				"drag.fileUpload": function(){
					thisBody.addClass("fileUploadDragStart");
				},
				"dragover.fileUpload":	function(event)
				{
					if (thisBody.hasClass("fileUploadDragStart")) return;
					$(this).find(".dragAndDropElement")
					.trigger("dragAndDropShow");
				},
				"dragleave.fileUpload":	function(event){
					_thisHide();
				},
				"mouseout.fileUpload": function(event){
					_thisHide();
				},
				"drop.fileUpload":		function(event){
				},
			});
			
			return $(this);
		}
		function _init(options, callback)
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
			return opts;
		}
		function _getBody(thisElm){
			return thisElm.is("body")?thisElm:thisElm.parents("body");
		}
		function _thisShow(thisElm, opts)
		{
			clearTimeout(DragAndDropTimer);
			DragAndDropTimer = 0;
			
			_create(thisElm, opts)
				.css(opts.cssDrag)
				.find(".imageUploadMessage")
				.html(opts.dragStartMessage)
				.show();
			
			return true;
		}
		//	Hide darg&drop message
		function _thisHide()
		{
			clearTimeout(DragAndDropTimer);
			DragAndDropTimer = setTimeout(_thisHideTimeout, 50);
		}
		function _thisHideTimeout()
		{
			clearTimeout(DragAndDropTimer);
			DragAndDropTimer = 0;
			_destroy($(".dragAndDropElement"));
			$("iframe").each(function(){
				_destroy($(this).contents().find(".dragAndDropElement"));
			});
		}
		function _create(thisElement, opts)
		{
			var ui = thisElement.find(".imageUploadField");
			if (ui.length) return ui;

			thisElement.css(opts.cssElm);
			
			var thisBody = _getBody(thisElement);
			//	Add UI holder
			ui = $('<div class="imageUploadField">')
				.css(opts.css)
				.appendTo(thisElement);
				
			if (thisElement.is("body")){
				thisElement.css({position: 'static'});
				ui.height(thisElement.parent().height());
			}else{
				ui.height(thisElement.height());
			}
			//	Supports UI elements
			$('<div class="imageUploadContent">' + opts.content + '</div>')
				.css(opts.cssContent)
				.appendTo(ui);
			$('<div class="imageUploadMessage"></div>')
				.css(opts.cssMessage)
				.hide()
				.appendTo(ui);
			//	Append input field under UI element
			$('<input type="file" name="imageFieldUpload[]" multiple />')
				//	Input styling
				.css(opts.cssInput)
				.attr("title", opts.message)
				.appendTo(ui)
				//	On change submit hidden form to hidden frame
				.change(function(){
					_drop(thisElement, opts);
				});
			return ui;
		}
		function _destroy(thisElement)
		{
			var thisBody = _getBody(thisElement);
			thisBody.removeClass("fileUploadDrag").removeClass("fileUploadDragStart");
			thisElement.find(".imageUploadField").remove();
		}
		function _drop(thisElement, opts)
		{
			var thisBody = _getBody(thisElement);
			
			if (thisBody.find("#imageUploadFrame").length == 0)	thisBody
				.append('<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>')
			if (thisBody.find("#imageUploadForm").length == 0)	thisBody
				.append('<form action="file_images_upload.htm" method="post" target="imageUploadFrame" id="imageUploadForm" enctype="multipart/form-data" style="display:none"></form>');
			//	Add onLoad event
			thisBody.find("#imageUploadFrame").unbind().load(function()
			{
				thisElement.trigger("dragAndDropHide");
				//	Callback with JSON data
				var responce = $(this).contents().find("body").html();
				opts.callback.call(thisElement, $.parseJSON(responce));
			});
			
			var ui = thisElement.find(".imageUploadField");
			var thisInput = ui.find("input[type=file]");

			thisElement.trigger("dragAndDropDrop");
			//	Temporary move input to hidden form
			thisBody.find("#imageUploadForm")
				.html('<input type="hidden" name="' + opts.uploadField + '" value="' + _uploadFolder(thisElement, opts) + '" />')
				.append(thisInput)
				.submit();
			//	After submit move input field back
			thisInput.appendTo(ui);
		}
		function _uploadFolder(thisElement, opts){
			//	Upload file folder
			var uploadFolder = opts.uploadFolder;
			if (uploadFolder) return uploadFolder;

			try{
				var cfg = $.parseJSON(thisElement.attr("rel"));
				return cfg['uploadFolder']?cfg['uploadFolder']:"";
			}catch(e){ }
			return "";
		}
	};
	// Plugin defaults – added as a property on our plugin function.
	$.fn.fileUpload.defaults = 
	{
		uploadFolder:	"",				// Server path to upload
		uploadField:	"fileImagesPath",
		callback:		function(responce) { console.log(responce); },	// Call after uploading
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
			right: 		0, bottom: 0,
			left:		0, top: 0,
			"z-index": 9999,
		},
		//	CSS style content box
		cssContent:	{
		},
		//	CSS style owner's object
		cssElm:{
			position: 		'relative',
		},
		//	CSS style owner drop holder
		cssDropHolder:	{
			"min-height":	"110px",
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
			background: "#ff6",
			color:		"green",
		},
		//	CSS style uploading message
		cssUpload:	{
			background: "green",
			color:		"white",
		},
		//	CSS style upload message box
		cssMessage:	{
			position:		"absolute",
			left: 			0,
			right: 			0,
			padding:		"10px 10px",
			margin:			"auto",
			"max-width":	"500px",
			"font-size":	"30px",
			"text-align":	"center",
			"text-shadow":	"1px 1px 2px rgba(0, 0, 0, 0.5)",
			border:			"dotted 3px green",
			"border-radius":"15px",
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
