<? function editor_FCK3(&$baseDir, &$baseFolder)
{
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