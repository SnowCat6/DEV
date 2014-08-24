<? function editor_FCK4(&$baseDir, &$baseFolder)
{
	m("script:jq");
	m("script:ajaxForm");
	m("script:editorFCK4finder",$baseDir);
	m("script:editorFCK4",		$baseDir);
} ?>
<? function makeCKStyleScript(&$script, $cssFile)
{
	$bOK 	= false;
	$f		= file_get_contents($cssFile);
	preg_match_all('#/\* (.*): ([\w]+)\.([\w\d\.]+) \*/#', $f, $vals);
	foreach($vals[1] as $ix => $name)
	{
		$n		= str_replace("'", '"', $name);
		$elm	= $vals[2][$ix];
		$class	= $vals[3][$ix];
		$class	= str_replace('.', ' ', $class);
		$script[$name]	= "	{ name: '$n', element: '$elm', attributes: { 'class': '$class' } }";
		$bOK	= true;
	}
	preg_match_all('#/\* (.*): ([\w]+) \*/#', $f, $vals);
	foreach($vals[1] as $ix => $name)
	{
		$n	= str_replace("'", '"', $name);
		$elm= $vals[2][$ix];
		$script[$name]	= "	{ name: '$n', element: '$elm' }";
		$bOK	= true;
	}
	return $bOK;
}?>
<? function script_editorFCK4(&$baseDir)
{
	$rootURL	= globalRootURL;
/******************************/
//	Build CSS JS rules
	$cssFiles	= getSiteFiles("", '\.css$');
	$styles		= array();
	$script		= array();
	foreach($cssFiles as $name=>$path){
		if (makeCKStyleScript($script, $path)){
			$styles[$name]	= "'$rootURL/$name'";
		}
	}
	$styles	= implode(", ", 	$styles);
	$script	= implode(",\r\n",	$script);
?>
<script>
/*<![CDATA[*/
function editorInsertHTML(instanceName, html)
{
	if (!instanceName){
		instanceName = $($(".submitEditor").get(0)).attr("name");
	}
	var oEditor = CKEDITOR.instances[instanceName];
	if (oEditor) oEditor.insertHtml(html);
}

$(function()
{
	if (typeof CKEDITOR == 'undefined'){
		window.CKEDITOR_BASEPATH = '{$rootURL}/{$baseDir}/';
		var CKEscript = '{$rootURL}/{$baseDir}/ckeditor.js';
	//	var CKEscript = '//cdn.ckeditor.com/4.4.4/standard/ckeditor.js';
	//	var CKEscript = '//cdn.ckeditor.com/4.4.4/full/ckeditor.js';
		$.getScript(CKEscript)
		.done(function(){
			$.getScript('{$rootURL}/{$baseDir}/adapters/jquery.js').done(CKEditorInitialise);
		});
	}else{
		CKEditorInitialise();
	}
});

function CKEditorInitialise(){
/*************************************/
<? if ($script){ ?>
try{
	CKEDITOR.config.allowedContent = true;
	CKEDITOR.config.contentsCss = ['{$rootURL}/{$baseDir}/contents.css', {$styles}];
	CKEDITOR.stylesSet.add('default', [{$script}]);
}catch(e){};
/*************************************/
try{
	AddFCKplugins();
}catch(e){}
<? } ?>
CKEDITOR.on('instanceReady', function(ev)
{
	var editor = ev.editor;
	
	editor.on('paste', function(evt) {
		evt.data.dataValue = cleanHTML(evt.data.dataValue);
		console.log(evt.data.dataValue);
	}, null, null, 9);
	
	CKEditorConfigDragAndDrop(editor);
	CKEditorConfigDragAndDropInline(editor);
});
/*************************************/
$("a#inlineEditor").click(function()
{
	var parent = $($(this).parents(".adminEditArea")[0]);
	var editable = parent.find(".inlineEditor");
	editable.each(function()
	{
		configureInlineEditor($(this));
	});
	
	$(editable[0]).focus();
	
	return false;
}).removeAttr("id");
/*************************************/
$(".inlineEditor")
.on("dragover", function()
{
	if ($(this).hasClass('FCKdrag')) return;
	$(this).unbind();
	var editor = configureInlineEditor($(this));
	CKEditorConfigDragAndDropInline(editor);
}).on("drag", function(){
	$(this).addClass('FCKdrag');
}).on("dragend", function(){
	$(this).removeClass('FCKdrag');
});
/*************************************/
	$("div.editor").attr("contenteditable", true);
	$("textarea.editor,div.editor").each(function()
	{
		configureEditor($(this));
	}).parents("form").submit(function(){
		return submitAjaxForm($(this), true);
	});
};
/***************************/
function configureEditor(thisElement)
{
	thisElement
		.removeClass("editor")
		.addClass("submitEditor");
	
	var height = Math.min(14 * thisElement.attr("rows"), $(window).height() - 300);
	
	try{
		var cfg = $.parseJSON(thisElement.attr("rel"));
	}catch(e){
		var cfg = new Array();
	};
	
	var baseFolder = cfg['folder'];

	if (baseFolder && editorBaseFinder){
		var cnn = editorBaseFinder+'{{getURL:file_fconnector/#folder#}}';
		cnn = cnn.replace(/#folder#/, baseFolder);
		var editor = thisElement.ckeditor({
			height: height,
			filebrowserWindowWidth : '800',
			filebrowserWindowHeight: '400',
			filebrowserBrowseUrl: cnn,
			filebrowserImageBrowseUrl: cnn + '&Type=Images'
		});
	}else{
		var editor = thisElement.ckeditor({
			height: height
		});
	}
	return editor;
}
function configureInlineEditor(thisElement)
{
	var data = thisElement.next();
	if (data.attr("id") == "editorData"){
		thisElement.html(data.text());
	}
	thisElement.attr("contenteditable", true);
	return configureEditor(thisElement);
}
// removes MS Office generated guff
function cleanHTML(input)
{
	// 1. remove line breaks / Mso classes
	var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g; 
	var output = input.replace(stringStripper, ' ');
	
	// 2. strip Word generated HTML comments
	var commentSripper = new RegExp('<!--(.*?)-->','g');
	var output = output.replace(commentSripper, '');
	
	// 3. remove tags leave content if any
	var tagStripper = new RegExp('<(/)*(meta|link|\\?xml:|st1:|o:)(.*?)>','gi');
	output = output.replace(tagStripper, '');
	
	// 4. Remove everything in between and including tags '<style(.)style(.)>'
	var badTags = [/*'applet','embed',' style', 'script', 'noscript', */'noframes'];
	for (var i=0; i< badTags.length; i++) {
		tagStripper = new RegExp('<'+badTags[i]+'.*?'+badTags[i]+'(.*?)>', 'gi');
		output = output.replace(tagStripper, '');
	}
	
	// 5. remove attributes ' style="..."'
	var badAttributes = [/*'style', */'start'];
	for (var i=0; i< badAttributes.length; i++) {
		var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"','gi');
		output = output.replace(attributeStripper, '');
	}
	//	6. Replace &nbsp; to space
	output = output.replace(/&nbsp;/gi, ' ');
	
	return output;
}
/*************************************/
function CKEditorConfigDragAndDropInline(editor)
{
	var eName = editor.name;
	$(".cke_editable_inline").each(function()
	{
		if ($(this).attr("title").indexOf(', ' + eName + '') < 0) return;
		
		$(this).on("dragover", function(event)
		{
			CKEditorDragAndDropBind(editor, $(this));
		}).on("drag", function(){
			$(this).addClass('FCKdrag');
		}).on("dragend", function(){
			$(this).removeClass('FCKdrag');
		});
	});
}
/*************************************/
function CKEditorConfigDragAndDrop(editor)
{
	var eName = editor.name;
	var eControl = $(document.getElementById("cke_" + eName));
	if (eControl == null) return;
	
	/**************************************/
	//	ADD UPLOAD FILES INTO CKEDITOR
	eControl.find(" .cke_wysiwyg_frame").contents().find("html")
	.on("dragover", function(event)
	{
		CKEditorDragAndDropBind(editor, $(this).find("body"));
	}).on("drag", function(){
		$(this).find("body").addClass('FCKdrag');
	}).on("dragend", function(){
		$(this).find("body").removeClass('FCKdrag');
	});
}
function CKEditorDragAndDropCSS(htmlElm)
{
	if ($(htmlElm).find("#CKEditorDragAndDropCSS").length) return;
	
	var htmlStyle = ''
		+"#fileUploadFCK { display:block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: yellow; color: black; }"
		+".FCKdrop #fileUploadFCK { background: green; color: white; }"
		+"#fileUploadFCK div { position:absolute; top: 50%; margin-top: -20px; text-align: center; font-size: 28px; width: 100%; }"
		+"#fileUploadFCK input { display:block; position:absolute;  width: 100%; height: 100%;  opacity: 0; filter:'alpha(opacity: 0)'; }"
		;

	$('<style id="CKEditorDragAndDropCSS">').html(htmlStyle).appendTo($(htmlElm).find("head"));
}
function CKEditorDragAndDropBind(editor, eBody)
{
	if (eBody.hasClass("FCKdrag")) return;
	
	CKEditorDragAndDropCSS(eBody.parents("html"));
	
	if (eBody.find("#fileUploadFCK").length > 0) return;
	
	var cfg = $.parseJSON($(editor.element).attr("rel"));
	var folder = cfg["folder"];
	if (folder){
		folder += "/Image";
	}
	
	$(
	'<iframe name="imageUploadFCK" id="imageUploadFCK" style="display:none"></iframe>'+
	'<form id="fileUploadFCK" action="{{url:file_images_upload}}" method="post" target="imageUploadFCK" enctype="multipart/form-data">'
	+'<input type="hidden" name="fileImagesPathFull" value="' + folder + '" />'
	+'<div>Вставить файл</div>'
	+'<input type="file" name="imageFieldUpload[]" multiple />')
		.appendTo(eBody);
	
	eBody.find("#fileUploadFCK input")
	.change(function(){
		eBody.addClass("FCKdrop");
		eBody.find("#fileUploadFCK div").html('Загрузка файла, подождите...');
		$(this).parent().submit();
	})
	.on("dragleave", function(){
		if (eBody.hasClass("FCKdrop")) return;
		eBody.find("#fileUploadFCK, #imageUploadFCK").remove();
	})
	
	eBody.find("#imageUploadFCK").load(function()
	{
		var ctx = $(this).contents().find("body").html();
		eBody.removeClass("FCKdrop");
		eBody.find("#fileUploadFCK, #imageUploadFCK").remove();
		
		try{
			var responce = $.parseJSON(ctx);
			for(fName in responce)
			{
				var c = responce[fName];
				if (c['error']){
					alert(c['error']);
					continue;
				}
				var path = c['path'];
				var size = c['dimension'].split(' x ');

				var value = '<img src="' + path + '"'
					+ ' width="' + size[0] + '"'
					+ ' height="' + size[1] + '"'
					+ ' />';

				editor.focus();
				editor.insertHtml(value);
				editor.fire( 'saveSnapshot' );
			}
		}catch(e){
		}
	});
}

 /*]]>*/
</script>
<? } ?>
<? function script_editorFCK4finder(&$baseDir)
{
	$browserVersion	= 1;
	if (is_dir($baseFinder = '~_editor/ckfinder.2.4')){
		$browserVersion = 2;
	}else
	if (is_dir($baseFinder = '_editor/CKFinder.1.2.3')){
	}else $baseFinder = '';
?>
<script>
<? if ($baseFinder){ ?>
<? if ($browserVersion == 1){ ?>
//	Function to insert selected image from FCKFinder 1.x
function SetUrl( url, width, height, alt )
{
	CKEDITOR.tools.callFunction(CKEditorFuncNum, url);
}
<? } ?>
var editorBaseFinder = "{$rootURL}/{$baseFinder}/ckfinder.html?Connector=";
$(function(){
	if (typeof CKFINDER == 'undefined'){
		$.getScript('{$rootURL}/{$baseFinder}/ckfinder.js').done(function(){
		});
	}
});
<? }else{ ?>
	var editorBaseFinder = null;
<? } ?>
</script>
<script>
//	Plug-ins
function AddFCKplugins(editor)
{
	FCKimageSelect();
	FCKinlinesave();
	CKEDITOR.config.extraPlugins = 'inlinesave,imageselect';
}
</script>
<script>
function htmlEncode( html )
{
	return String(html)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
};

function FCKimageSelect()
{
CKEDITOR.config.imageselect_button_label = 'Картинки';
CKEDITOR.config.imageselect_button_title = 'Вставить картинку';
CKEDITOR.config.imageselect_button_voice = 'Вставить картинку';

if (typeof window.globalFolders == 'undefined') window.globalFolders = new Array();

CKEDITOR.plugins.add('imageselect',
{
	requires : ['richcombo'],
	init : function( editor )
	{
		try{
			var element = $(editor.element);
			var cfg = $.parseJSON(element.attr("rel"));
			var folder = cfg["folder"];
			if (!folder) return;
			editor.config.cfg = cfg;

			if (!window.globalFolders[folder])
			{
				window.globalFolders[folder] = new Array();
				$.ajax('file_images_get.htm?fileImagesPathFull=' + folder).done(function(data){
					window.globalFolders[folder] = $.parseJSON(data);
				});
			}
		}catch(e){
			return;
		}

		var config = editor.config;
		// Gets the list of insertable strings from the settings.
		var strings = config.imageselect_strings;
		// add the menu to the editor
		editor.ui.addRichCombo('strinsert',
		{
			label: 		config.imageselect_button_label,
			title: 		config.imageselect_button_title,
			voiceLabel: config.imageselect_button_voice,
			toolbar: 	'insert',
			className: 	'cke_format',
			multiSelect:false,
			panel:
			{
				css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
				voiceLabel: editor.lang.panelVoiceLabel
			},

			init: function()
			{
				var cfg = editor.config.cfg;
				var folder = cfg["folder"];

				var folders = window.globalFolders[folder];
				for(var group in folders)
				{
					this.startGroup( group );
					var files = folders[group];
					for(var file in files)
					{
						value = files[file]['size']+':'+files[file]['path'];
						this.add(value, file, files[file]['size']);
					}
				}
			},

			onClick: function( value )
			{
				var o = value.split(':', 2);
				var size = o[0].split('x');
				var path = o[1];
				
				var value = '<img src="' + path + '"'
					+ ' width="' + size[0] + '"'
					+ ' height="' + size[1] + '"'
					+ ' />';
				
				editor.focus();
				editor.fire( 'saveSnapshot' );
				editor.insertHtml(value);
				editor.fire( 'saveSnapshot' );
			},

		});
	}
});
}
function FCKinlinesave()
{
CKEDITOR.plugins.add( 'inlinesave',
{
	init: function( editor )
	{
		try{
			var element = $(editor.element);
			var cfg = $.parseJSON(element.attr("rel"));
			var action = cfg["action"];
			if (!action) return;
			editor.config.cfg = cfg;
		}catch(e){
			return;
		}
		editor.on('change', function(){
			var cmd = editor.getCommand( 'inlinesave' );
			cmd.enable();
		});
		
		editor.addCommand( 'inlinesave',
			{
				exec : function( editor )
				{
					var cfg = editor.config.cfg;
					var action = cfg["action"];
					var field = cfg['dataName'];
					if (!field) field = 'editorData';
					cfg[field] = editor.getData();

					var cmd = editor.getCommand( 'inlinesave' );
					cmd.disable();
					jQuery.ajax({
						type: "POST",
						url: action,
						data: cfg
					})
					.done(function (data, textStatus, jqXHR) {
						var element = $(editor.element);
						element.attr("contenteditable", false);
						editor.destroy();
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						cmd.enable();
						alert("Error saving content.");
					});   
				}
			});
		editor.ui.addButton( 'Inlinesave',
		{
			label: 'Save',
			toolbar: 	'document',
			command: 'inlinesave',
			icon: '<?= globalRootURL?>/design/inlinesave.png'
		} );
	}
} );
}
</script>
<? } ?>