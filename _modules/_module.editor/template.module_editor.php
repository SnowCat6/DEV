<?
function module_editor($val, &$baseFolder)
{
	if ($val){
		list($fn, $val) = explode(':', $val, 2);
		$fn = getFn("editor_$fn");
		return $fn?$fn($val, $baseFolder):NULL;
	}
	
	noCache();


	if (is_dir($baseDir = '_editor/ckeditor'))
		return FCK4_1($baseDir, $baseFolder, $data);

	m('script:jq');
	m('script:ajaxForm');

	//	FCK Edit
	$baseDir	= '_editor/CKEditor.3.0';
	$baseName	= 'ckeditor.js';
	$baseVersion= 3;

	if (!is_dir($baseDir)){
		$baseDir	= '_editor/FCKEditor.2.6.3';
		$baseName	= 'fckeditor.js';
		$baseVersion= 2;
	};
	
	if (!is_dir($baseDir)){
		$baseDir	= '_editor/FCKeditor2.5.1';
		$baseName	= 'fckeditor.js';
		$baseVersion= 2;
	};

	//	FCK Finder
	$baseFinder = '_editor/CKFinder.1.2.3';
	if (!is_dir($baseFinder)) $baseFinder = '';
?>
<meta http-equiv="X-UA-Compatible" content="IE=5">
<script language="JavaScript" type="text/javascript" src="<?= globalRootURL?>/<?= "$baseDir/$baseName"?>"></script>
<script language="javascript" type="text/javascript">

function doEdit(name, h)
{
	h *= 14;
	h = Math.min(h, $(window).height() - 300);
	
	var RootPath	= '<?= globalRootURL ?>';
	var BasePath	= '<?= globalRootURL.'/'.$baseDir?>';
	var BaseVersion	= <?= $baseVersion?>;
	var ImageFolder	= '<?= $baseFolder?>';
	var Browser		= '<?= $baseFinder?>';

	//Init FCK Editor
	oFCKeditor = new FCKeditor(name, '', h+80, 'BasicEx');
	oFCKeditor.BasePath	= BasePath+'/';

	oFCKeditor.Config['ImageUpload'] = false;
	oFCKeditor.Config['FlashUpload'] = false;
	oFCKeditor.Config['LinkUpload']  = false;

	var cnn = '../filemanager/browser/default/browser.html?Connector='+RootPath+'/file_connector.htm&ServerPath='+ImageFolder;
	
	oFCKeditor.Config['ImageBrowserURL']= cnn+'&Type=Image';
	oFCKeditor.Config['ImageUploadURL'] = RootPath+'/file_upload.htm?Type=Image';

	oFCKeditor.Config['FlashBrowserURL']=cnn + '&Type=Flash';
	oFCKeditor.Config['FlashUploadURL'] = RootPath+'/file_upload.htm?Type=Flash';

	oFCKeditor.Config['LinkBrowserURL'] = cnn;

	try{
		if (Browser){
			var mN=document.location.protocol+'//'+document.location.host;
			var cnn = RootPath+'/'+Browser+'/ckfinder.html?Connector='+mN+RootPath+'/file_fconnector.htm&ServerPath='+ImageFolder;
			//	CKFinder
			oFCKeditor.Config['ImageBrowserURL'] = cnn + '&type=Image';
			oFCKeditor.Config['FlashBrowserURL'] = cnn + '&type=Flash';
			oFCKeditor.Config['LinkBrowserURL']  = cnn;
		}
	}catch(e){};

//	Build edit
	oFCKeditor.ReplaceTextarea();
}

$(function(){
	$("textarea.editor").each(function(){
		$(this).removeClass("editor").addClass("submitEditor");
		doEdit($(this).attr('name'), $(this).attr('rows'));
	});
});

// called when FCKeditor is done starting..
function FCKeditor_OnComplete( editorInstance ){
        //this is how you can assign onsubmit action
        editorInstance.LinkedField.form.onsubmit = function(){
			editorInstance.UpdateLinkedField();
			editorInstance.Events.FireEvent( 'OnAfterLinkedFieldUpdate' );
			return submitAjaxForm(editorInstance.LinkedField.form, true);
		};
}
function editorInsertHTML(instanceName, html)
{
	if (!instanceName){
		instanceName = $($(".submitEditor").get(0)).attr("name");
	}
	
	var oEditor = FCKeditorAPI.GetInstance(instanceName);
	if (oEditor) oEditor.InsertHtml(html);
}
</script>
<? } ?>
<? function FCK4_1(&$baseDir, &$baseFolder, &$data)
{
	$rootURL = globalRootURL;
	m("script:jq");
	m("script:ajaxForm");
	
	$browserVersion	= 1;
	if ($baseFolder){
		if (is_dir($baseFinder = '~_editor/ckfinder.2.4')){
			$browserVersion = 2;
		}else
		if (is_dir($baseFinder = '_editor/CKFinder.1.2.3')){
		}else $baseFinder = '';
	}
?>
<?
//	Build CSS JS rules
$cssFiles	= getFiles(array($baseDir, cacheRootPath), '\.css$');
$styles		= array();
$script		= array();
foreach($cssFiles as $path){
	if (makeCKStyleScript($script, $path)){
		$name			= str_replace(cacheRootPath . '/', '', $path);
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
<? if ($baseFinder && $browserVersion == 1){ ?>
//	Function to insert selected image from FCKFinder 1.x
function SetUrl( url, width, height, alt )
{
	CKEDITOR.tools.callFunction(CKEditorFuncNum, url);
}
<? } ?>

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
<? if ($baseFinder){ ?>
	if (typeof CKFINDER == 'undefined'){
		$.getScript('{$rootURL}/{$baseFinder}/ckfinder.js').done(function(){
		});
	}
<? } ?>
});

function CKEditorInitialise(){
<? if ($script){ ?>
try{
	CKEDITOR.config.allowedContent = true;
	CKEDITOR.config.contentsCss = ['{$rootURL}/{$baseDir}/contents.css', {$styles}];
	CKEDITOR.stylesSet.add('default', [{$script}]);
}catch(e){}
<? } ?>
$("a#inlineEditor").click(function()
{
	var parent = $($(this).parents(".adminEditArea")[0]);
	var editable = $(parent.find(".inlineEditor")[0]);
	editable.attr("contenteditable", true);
	configureEditor(editable);
	$(editable).focus();
	return false;
}).removeAttr("id");
/*************************************/
CKEDITOR.config.extraPlugins = 'inlinesave';
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
	if (!baseFolder) baseFolder = "{$baseFolder}";

	if (baseFolder){
		var cnn = '{$rootURL}/{$baseFinder}/ckfinder.html?Connector={{getURL:file_fconnector/#folder#}}';
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
}
 /*]]>*/
</script>
<? } ?>
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
