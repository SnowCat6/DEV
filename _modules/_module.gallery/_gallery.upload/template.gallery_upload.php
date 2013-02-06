<?
function gallery_upload($val, $data)
{
		
	if ($val == 'documentTitleUpload')
		return galleryUploadForm($data[1]);
		
	if ($val == 'documentTitle'){
			
		$db		= module('doc', $data);
		$id		= $db->id();
		$folder	= $db->folder();
		$action = "gallery_upload_documentTitle$id";
	}else{
		$folder	= $data;
		$action = "gallery_upload_files";
	}
?>
<iframe src="<?= getURL($action)?>" allowtransparency="1" frameborder="0" width="250" height="250px"></iframe>
<? } ?>
<?
function galleryUploadForm($id)
{
	$id	= (int)$id;
	$db	= module('doc');
	if (!access('write', "doc:$id")) return;
	
	$folder	= $db->folder($id);
	$action = "gallery_upload_documentTitle$id";

	setTemplate('form');
	if (modFileAction($folder, true)){
		module("doc:recompile:$id");
	}

	@list($name, $path) = each(getFiles("$folder/Title"));
?>
<style>
body{
	color:white;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
}
</style>
<form action="<?= getURL($action)?>" method="post" enctype="multipart/form-data">
<div>Обложка</div>
<? if ($name){ ?>
<div style="background:#006600; padding:6px 5px"><input name="modFile[files][Title][]" id="titleFile" type="checkbox" value="{$name}" />
<label for="titleFile"><b>{$name}</b></label></div>
<? }else{ ?>
<div style="background:#900; padding:6px 5px"><b>не загружена</b></div>
<? } ?>
<div>Загрузить файл для обложки</div>
<div><input name="modFileUpload[Title][]" type="file" /></div>
<p><input type="submit" name="modFile[delButton]" class="button w100" value="Установть обложку" /></p>
</form>
<? } ?>