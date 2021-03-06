// JavaScript Document

// var CK4Styles;
// var CK4Scripts;
// var CK4RootURL;
/*************************************/
$(function()
{
//	if (typeof window.CKEDITOR_BASEPATH == 'undefined')
	if (typeof CKEDITOR == 'undefined')
	{
		if (typeof CK4RootURL == 'undefined'){
			window.CKEDITOR_BASEPATH = '_editor/ckeditor/';
		}
		else window.CKEDITOR_BASEPATH = CK4RootURL;
		window.CKEDITOR_LOCATION = window.CKEDITOR_BASEPATH;

//		window.CKEDITOR_BASEPATH = '//cdn.ckeditor.com/4.4.7/standard/';
//		window.CKEDITOR_BASEPATH = '//cdn.ckeditor.com/4.4.7/full/';
		$.getScript(window.CKEDITOR_BASEPATH + 'ckeditor.js').done(function()
		{
			$.getScript(window.CKEDITOR_BASEPATH + 'adapters/jquery.js')
				.done(CKEditorInitialise);
		});
	}else{
		CKEditorInitialise();
	}
});
/*************************************/
function CKEditorInitialise()
{
	try{
//		CKEDITOR.location = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
		CKEDITOR.configPath = window.CKEDITOR_LOCATION + '../ckeditor_config.js';
		CKEDITOR.config.allowedContent = true;
		CKEDITOR.config.contentsCss = CK4Styles;
		CKEDITOR.stylesSet.add('default', CK4Scripts);
		/*************************************/
		FCKimageSelect();
		FCKinlinesave();
		FCKwidgetAdd();
		FCKmedia();
		CKEDITOR.config.extraPlugins = 'inlinesave,imageselect,cmswidget,mediaembed,codemirror';
	}catch(e){	}
/*************************************/
	$("a#inlineEditor")
	.removeAttr("id")
	.click(function()
	{
		$(this).closest(".adminEditArea").find(".inlineEditor").each(function(ndx)
		{
			configureInlineEditor($(this));
			if (ndx === 0){
				$(this).focus();
			}
		});
			
		return false;
	});
/*************************************/
	$("textarea.editor").each(function()
	{
		$(this).removeClass("editor").addClass("submitEditor");
		configureEditor($(this));
	}).closest("form").submit(function(){
		return submitAjaxForm($(this), true);
	});
/*************************************/
	$(".inlineEditor").on("dragover", function(){
		configureInlineEditor($(this));
	}).on("dblclick", function(e){
		var e2 = configureInlineEditor($(this));
		if (e2) e2.focus();
		e.stopPropagation();
	});
/*************************************/
	CKEDITOR.on('instanceReady', function(ev)
	{
		var editor = ev.editor;
		configureEditableContainer(editor);
		
		editor.on('paste', function(evt) {
			evt.data.dataValue = cleanHTML(evt.data.dataValue);
		}, null, null, 9);
		
		editor.on("mode", function(e)
		{
			switch(editor.mode){
			case 'wysiwyg':
				configureEditableContainer(e.editor);
				break;
			}
		});
		
	});
}
function configureEditableContainer(editor)
{
	//	Текущий контейнер
	var elm = $(document.getElementById(editor.container.getId()));
	//	Найти редактируемую область
	var elmContainer = elm.find("iframe");
	if (elmContainer.length === 0){
		//	Для инлайн элементов
		editor.editableContainer = elm;
	}else{
		//	Для IFRAME элементов
		editor.editableContainer = elmContainer.contents().find("body");
	}
	
	CKEditorCinfigBackground(editor);
	CKEditorCinfigDragAndDrop(editor);
}
/***************************/
function configureEditor(thisElement)
{
	var cfg;
	try{
		cfg = $.parseJSON(thisElement.attr("rel"));
	}catch(e){
		cfg = new Array();
	}
	thisElement.uniqueId();
	
	var height = Math.min(14 * thisElement.attr("rows"), $(window).height() - 350);
	var baseFolder = cfg['folder'];
try{
	if (baseFolder && typeof(editorBaseFinder) === 'string')
	{
		var c  = cnn.replace(/#folder#/, baseFolder);
		return thisElement.ckeditor({
			height: height,
			customConfig: CKEDITOR.configPath,
			filebrowserWindowWidth : '800',
			filebrowserWindowHeight: '400',
			filebrowserBrowseUrl: c,
			filebrowserImageBrowseUrl: c + '&Type=Images'
		});
	}
	return thisElement.ckeditor({
		height: height,
		customConfig: CKEDITOR.configPath,
	});
}catch(e){
	alert("Ошибка создания редактора");
}
}
function configureInlineEditor(thisElement)
{
	if (thisElement.attr("contenteditable") == "true") return;
	
	var data = thisElement.next();
	if (data.attr("id") == "editorData"){
		thisElement.html(data.text());
		data.remove();
	}
	thisElement.attr("contenteditable", "true");
	return configureEditor(thisElement);
}
/***************************/
function CKEditorCinfigDragAndDrop(editor)
{
	var folder;
	try{
		var cfg = $.parseJSON($(editor.element).attr("rel"));
		folder = cfg["folder"];
		if (folder == "") return;
	}catch(e){
		console.log('CKEditor no JSON');
		return;
	}

	folder += '/Image';
	editor.editableContainer
	.fileUpload("d&d",
	{
		uploadFolder:	folder,
		uploadField:	"fileImagesPathFull",
		callback:		function(responce)
		{
			for(var image in responce)
			{
				var prop = responce[image];
				if (prop['error']){
					alert(prop['error']);
					continue;
				}
				var dimension = prop['dimension'];
				var path = prop['path'];
				
				var size = dimension.split(' x ');
				html = '<img src="' + path + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';

				editor.focus();
				editor.insertHtml(html);
				editor.fire('saveSnapshot');
			}
		}
	});
}
function CKEditorCinfigBackground(editor)
{
	try{
		var cfg = $.parseJSON($(editor.element).attr("rel"));
	}catch(e){
		console.log('CKEditor no JSON');
		return;
	}
	
	var b = editor.editableContainer;
	if (cfg['css'])		b.css(cfg['css']);
	if (cfg['class'])	b.addClass(cfg['class']);
}
/**************************************/
//	TOOLS
/**************************************/
// removes MS Office generated guff
function cleanHTML(output)
{
	// 1. remove line breaks / Mso classes
	if (FormatCfg['MS_WORD_disable'] != 'yes'){
		var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g; 
		var output = output.replace(stringStripper, ' ');
	}
	
	// 2. strip Word generated HTML comments
	if (FormatCfg['HTML_comments_disable'] != 'yes'){
		var commentSripper = new RegExp('<!--(.*?)-->','g');
		var output = output.replace(commentSripper, '');
	}
	
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
	
	//	6. Remove style bad property
	var badStyleProperty =['line-height'];
	if (FormatCfg['STYLE_font_disable'] != 'yes')		badStyleProperty.push('font-family');
	if (FormatCfg['STYLE_color_disable'] != 'yes')		badStyleProperty.push('color');
	if (FormatCfg['STYLE_font-size_disable'] != 'yes')	badStyleProperty.push('font-size');

	for (var i=0; i< badStyleProperty.length; i++) {
		var attributeStripper = new RegExp(badStyleProperty[i] + '\s*:\s*(.*?)[;"]','gi');
		output = output.replace(attributeStripper, '');
	}
	output = output.replace(/style\s*=\s*["']["']/gi, '');
	
	//	7. Replace &nbsp; to space
	if (FormatCfg['STYLE_nbsp_disable'] != 'yes'){
		output = output.replace(/&nbsp;/gi, ' ');
	}
	
	return output;
}
/*************************************/
function htmlEncode( html )
{
	return String(html)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
};
/*************************************/
function editorInsertHTML(instanceName, html)
{

	if (!instanceName)
		instanceName = $($(".submitEditor").get(0)).attr("id");

	var oEditor = CKEDITOR.instances[instanceName];
	if (oEditor) oEditor.insertHtml(html);
}
/*************************************/
function FCKimageSelect()
{
CKEDITOR.config.imageselect_button_label = 'Картинки';
CKEDITOR.config.imageselect_button_title = 'Вставить картинку';
CKEDITOR.config.imageselect_button_voice = 'Вставить картинку';

if (typeof window.globalFolders == 'undefined')
	window.globalFolders = new Array();

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
						this.add(
							folder + ':' + group + ':' + file,	// value
							file,	// name
							files[file]['size']	// title
						);
					}
				}
			},

			onClick: function( value )
			{
				var o = value.split(':', 3);
				var file	= window.globalFolders[o[0]][o[1]][o[2]];
				
				var size = file['size'].split('x');
				var path = file['path'];
				
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
/*************************************/
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
						editor.destroy();
						element.attr("contenteditable", false);
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						cmd.enable();
						alert("Error saving content.");
					});   
				}
			});
		editor.ui.addButton('Inlinesave',
		{
			label: 'Save',
			toolbar: 'document',
			command: 'inlinesave',
			icon: window.CKEDITOR_LOCATION + '../../design/inlinesave.png'
		});
	}
} );
}
/*************************************/
function FCKwidgetAdd()
{
if (typeof  window.widgetsLayout == 'undefined')
	window.widgetsLayout = new Array();

CKEDITOR.plugins.add('cmswidget', {
    requires: 'widget',
    init: function(editor)
	{
		editor.widgets.add('cmswidget',
		{

			init: function()
			{
				// Note that placeholder markup characters are stripped for the name.
				this.setData( 'name', this.element.getText());
				FCKwidgetQuerySnippet(editor, this.data.name);
			},
			data: function(){
				var widgetLayout = window.widgetsLayout[this.data.name];
				if (typeof widgetLayout == 'undefined'){
					widgetLayout = '';
				}
				
				this.element.setHtml(
'<span class="cms_snippet_name">' + this.data.name + '</span>' +
'<span class="cms_snippet_content">' + widgetLayout +  '</span>'
				);
			},
			downcast: function() {
				return new CKEDITOR.htmlParser.text( this.data.name );
			},
		});
    },
	afterInit: function(editor)
	{
		var placeholderReplaceRegex = /\[\[([^\[\]])+\]\]/g;
		editor.dataProcessor.dataFilter.addRules(
		{
			text: function( text, node )
			{
				return text.replace( placeholderReplaceRegex, function( match )
				{
					// Creating widget code.
					var innerElement = new CKEDITOR.htmlParser.element('span', {class: 'cms_snippet'});
					innerElement.add(new CKEDITOR.htmlParser.text( match ));

					return editor.widgets.wrapElement(innerElement, 'cmswidget').getOuterHtml();
				})
			}
		});
	}
} );
};

function FCKwidgetQuerySnippet(editor, snippetName)
{
/*
	setTimeout(function(){
		$.get('admin_snippet_get.htm', {
			name: snippetName
		})
		.done(function(data){
			 window.widgetsLayout[snippetName] = snippetName;
		});
	}, 10);
*/
}
/***************************/
function FCKmedia()
{

( function() {
    CKEDITOR.plugins.add( 'mediaembed',
    {
        icons: 'mediaembed', // %REMOVE_LINE_CORE%
        hidpi: true, // %REMOVE_LINE_CORE%
        init: function( editor )
        {
			editor.widgets.add( 'MediaEmbed',
			{
				dialog: 'MediaEmbedDialog',
				upcast: function( element ) {
					return element.name == 'div' && element.hasClass( 'MediaEmbed' );
				},
				data: function(){
				}
			} );

           var me = this;
           CKEDITOR.dialog.add( 'MediaEmbedDialog', function (instance)
           {
              return {
                 title : 'Embed Media',
                 minWidth : 550,
                 minHeight : 200,
                 contents :
                       [
                          {
                             id : 'iframe', expand : true,
                             elements :[
							 {
                                id: 	'embedArea', type: 	'textarea',
								rows: 	12, autofocus: 'autofocus',
                                label: 	'Paste Embed Code Here',
                                setup: function(widget){
									this.setValue(widget.element.getHtml());
                                },
                                commit: function(widget){
									widget.element.setHtml(this.getValue());
                                }
                              }]
                          }
                       ],
                  onOk: function()
				  {

						value = this.getContentElement('iframe', 'embedArea').getValue();
						value = '<div class="MediaEmbed">' + value + '</div>';
					  
						editor.focus();
						editor.fire( 'saveSnapshot' );
						editor.insertHtml(value);
						editor.fire( 'saveSnapshot' );
                  }
              };
           });

            editor.addCommand( 'MediaEmbed', new CKEDITOR.dialogCommand( 'MediaEmbedDialog',
                { allowedContent: 'iframe[*]' }
            ) );

            editor.ui.addButton( 'MediaEmbed',
            {
                label: 'Embed Media',
                command: 'MediaEmbed',
                toolbar: 'insert',
				icon: window.CKEDITOR_LOCATION + '../../design/mediaembed.png'
            } );
        }
    } );
} )();

};
