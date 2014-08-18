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
CKEDITOR.on('instanceReady', function(ev) {
	ev.editor.on('paste', function(evt) {
		evt.data.dataValue = cleanHTML(evt.data.dataValue);
		console.log(evt.data.dataValue);
	}, null, null, 9);
});
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