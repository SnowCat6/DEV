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
	window.CKEDITOR_BASEPATH = '{$rootURL}/{$baseDir}/';
	if (typeof CKEDITOR == 'undefined'){
		$.getScript('{$rootURL}/{$baseDir}/ckeditor.js').done(function(){
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
<? } ?>
CKEDITOR.on('instanceReady', function(ev)
{
	var editor = ev.editor;
	
	editor.on('paste', function(evt) {
		evt.data.dataValue = cleanHTML(evt.data.dataValue);
		console.log(evt.data.dataValue);
	}, null, null, 9);
	
	CKEditorConfigDragAndDrop(editor);
});
/*************************************/
function CKEditorConfigDragAndDrop(editor)
{
	var element = $(editor.element);
	var cfg = $.parseJSON(element.attr("rel"));
	var folder = cfg["folder"];
	if (folder){
		folder = folder.replace(/^[^/]+\/[^/]+/, '');
		folder += "/Image";
	}

	var eName = editor.name;
	var eControl = $(document.getElementById("cke_" + eName));
	/**************************************/
	//	ADD UPLOAD FILES INTO CKEDITOR
	eControl.find(" .cke_wysiwyg_frame").contents().find("html")
	.on("dragover.fileUploadFCK", function()
	{
		var thisElm = $(this).find("body");
		if (thisElm.find("#fileUploadFCK").length) return;
		
		var htmlStyle = ''
			+"#fileUploadFCK { display:block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: yellow; color: black; }"
			+".uploading #fileUploadFCK { background: green; color: white; }"
			+"#fileUploadFCK div { position:absolute; top: 50%; text-align: center; font-size: 28px; width: 100%; }"
			+"#fileUploadFCK input { display:block; position:absolute;  width: 100%; height: 100%;  opacity: 0; filter:'alpha(opacity: 0)'; }"
			;
		$("<style>").html(htmlStyle).appendTo($(this).find("head"));

		$(
		'<iframe name="imageUploadFCK" id="imageUploadFCK" style="display:none"></iframe>'+
		'<form id="fileUploadFCK" action="{{url:file_images_upload}}" method="post" target="imageUploadFCK" enctype="multipart/form-data">'
		+'<input type="hidden" name="fileImagesPath" value="' + folder + '" />'
		+'<div>Перетащите файл сюда</div>'
		+'<input type="file" name="imageFieldUpload[]" multiple />')
			.appendTo(thisElm);
		
		thisElm.find("#fileUploadFCK input")
		.change(function(){
			thisElm.addClass("uploading");
			thisElm.find("#fileUploadFCK div").html('Загрузка файла, подождите...');
			$(this).parent().submit();
		})
		.on("dragleave", function(){
			if (thisElm.hasClass("uploading")) return;
			thisElm.find("#fileUploadFCK, #imageUploadFCK").remove();
		})
		
		thisElm.find("#imageUploadFCK").load(function()
		{
			thisElm.removeClass("uploading");
			try{
				var responce = $.parseJSON($(this).contents().find("body").html());
				for(fName in responce)
				{
					var c = responce[fName];
					if (c['error']){
						alert(c['error']);
						continue;
					}
					var path = c['path'];
					var size = c['dimension'].split('x');;

					var value = '<img src="' + path + '"'
						+ ' width="' + size[0] + '"'
						+ ' height="' + size[1] + '"'
						+ ' />';

					editor.focus();
					editor.fire( 'saveSnapshot' );
					editor.insertHtml(value);
					editor.fire( 'saveSnapshot' );
				}
			}catch(e){
			}
			thisElm.find("#fileUploadFCK, #imageUploadFCK").remove();
		});
	});
}
/*************************************/
$("a#inlineEditor").click(function()
{
	var parent = $($(this).parents(".adminEditArea")[0]);
	var editable = parent.find(".inlineEditor");
	editable.each(function()
	{
		var data = $(this).next();
		if (data.attr("id") == "editorData"){
			$(this).html(data.text());
		}
		$(this).attr("contenteditable", true);
		configureEditor($(this));
	});
	
	$(editable[0]).focus();
	
	return false;
}).removeAttr("id");
/*************************************/
	try{
		AddFCKplugins();
		CKEDITOR.config.extraPlugins = 'inlinesave,imageselect';
	}catch(e){}
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
function AddFCKplugins()
{
}
</script>
<? } ?>