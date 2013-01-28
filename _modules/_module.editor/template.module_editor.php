<?
function module_editor($baseFolder, &$data)
{
	module('script:jq');
?>
<script language="JavaScript" type="text/javascript">
	var RootPath ='<?= globalRootURL ?>/';
	var ImageFolder ='<?= $baseFolder?>/';
</script>

<script language="JavaScript" type="text/javascript">
<?
//	FCK Edit
$baseDir = '_editor/CKEditor.3.0';
$baseName= 'ckeditor.js';
$baseVersion = 3;

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
?>
	var BasePath = RootPath + '<?= $baseDir?>/';
	var BaseVersion = <?= $baseVersion?>;
<?
	//	FCK Finder
	$baseFinder = '_editor/CKFinder.1.2.3';
	if (!is_dir($baseFinder)) $baseFinder = '';
?>
	var browser = '<?= $baseFinder?>';
</script>

<script language="JavaScript" type="text/javascript" src="<?= "$baseDir/$baseName"?>"></script>
<script language="JavaScript" type="text/javascript" src="_editor/editFck.js"></script>

<div id="formReadMessage" class="message" style="display:none">Документ записан</div>
<div id="formReadMessage" class="message error" style="display:none">Ошибка записи</div>

<script language="javascript">
function submitReadEdit()
{
	$("#formReadMessage").hide();
	        
	for (var fckname in FCKeditorAPI.Instances ){
		FCKeditorAPI.GetInstance(fckname).UpdateLinkedField();
		FCKeditorAPI.GetInstance(fckname).Events.FireEvent( 'OnAfterLinkedFieldUpdate' );
	}

	var hasAjaxForm = false;
	$(".ajaxForm").each(function()
	{
		hasAjaxForm = true;
		$.post($(this).attr("action"), $(this).serialize())
			.success(function(){
				$("#formReadMessage").show();
			})
			.error(function(){
				$("#formReadMessage.error").show();
			});
	});
	
	return hasAjaxForm == false;
};
// called when FCKeditor is done starting..
function FCKeditor_OnComplete( editorInstance ){
        //this is how you can assign onsubmit action
        editorInstance.LinkedField.form.onsubmit = submitReadEdit;
}
</script>

<? } ?>