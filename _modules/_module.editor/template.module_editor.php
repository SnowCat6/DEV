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

	module('script:jq');
	module('script:ajaxForm');

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
	
//	if (!is_dir($baseFinder2 = '_editor/ckfinder.2.4'))	$baseFinder2 = '';
	if (!$baseFinder2 && !is_dir($baseFinder = '_editor/CKFinder.1.2.3'))	$baseFinder = '';
?>

<script type="text/javascript" src="{$rootURL}/{$baseDir}/ckeditor.js"></script>

<? if ($baseFinder2){ ?>
<script type="text/javascript" src="{$rootURL}/{$baseFinder2}/ckfinder.js"></script>
<? } ?>

<? if ($baseFinder){ ?>
<script type="text/javascript" src="{$rootURL}/{$baseFinder}/ckfinder.js"></script>
<? } ?>
<?
//	Build CSS JS rules
$cssFiles	= getFiles(array(
	$baseDir,
	localCacheFolder.'/'.localSiteFiles
	), '\.css$');
$styles		= array();
$script		= array();
foreach($cssFiles as $path){
	if (makeCKStyleScript($script, $path)){
		$name			= str_replace(localCacheFolder.'/'.localSiteFiles.'/', '', $path);
		$styles[$name]	= "'$rootURL/$name'";
	}
}
$styles	= implode(", ", $styles);
$script	= implode(",\r\n", $script);
?>

<script>
//	Function to insert selected image from FCKFinder
function SetUrl( url, width, height, alt )
{
	CKEDITOR.tools.callFunction(CKEditorFuncNum, url);
}
function editorInsertHTML(instanceName, html)
{
	if (!instanceName){
		instanceName = $($(".submitEditor").get(0)).attr("name");
	}
	var oEditor = CKEDITOR.instances[instanceName];
	if (oEditor) oEditor.insertHtml(html);
}

$(function(){
<? if ($script){ ?>
try{
	CKEDITOR.config.contentsCss = [{$styles}];
	CKEDITOR.stylesSet.add('default', [{$script}]);
}catch(e){}
<? } ?>
	$("textarea.editor").each(function()
	{
		$(this).removeClass("editor").addClass("submitEditor");
		var height = Math.min(14 * $(this).attr("rows"), $(window).height() - 300);
<? if ($baseFinder2){ ?>
		var editor = CKEDITOR.replace($(this).attr('name'), {
			height: height,
			filebrowserWindowWidth : '800',
			filebrowserWindowHeight: '400',
		});
		CKFinder.setupCKEditor(editor, '{$rootURL}/{$baseFinder2}/', {
			connectorPath: '/'
		});
<? } ?>
<? if ($baseFinder){ ?>
		var cnn = '{$rootURL}/{$baseFinder}/ckfinder.html?Connector={{getURL:file_fconnector}}&ServerPath={$baseFolder}';
		var editor = CKEDITOR.replace($(this).attr('name'), {
			height: height,
			filebrowserWindowWidth : '800',
			filebrowserWindowHeight: '400',
			filebrowserBrowseUrl: cnn,
			filebrowserImageBrowseUrl: cnn + '&Type=Images',
			filebrowserFlashBrowseUrl: cnn + '&Type=Flash',
			filebrowserUploadUrl	 : '{{getURL:file_connector}}?Type=Files',
			filebrowserImageUploadUrl: '{{getURL:file_connector}}?Type=Images',
			filebrowserFlashUploadUrl: '{{getURL:file_connector}}?Type=Flash'
		});
<? } ?>
	});
});
</script>
<? } ?>
<? function makeCKStyleScript(&$script, $cssFile)
{
	$bOK 	= false;
	$f		= file_get_contents($cssFile);
	preg_match_all('#/\* (.*): ([\w]+)\.([\w\d]+) \*/#', $f, $vals);
	foreach($vals[1] as $ix => $name)
	{
		$n		= str_replace("'", '"', $name);
		$elm	= $vals[2][$ix];
		$class	= $vals[3][$ix];
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