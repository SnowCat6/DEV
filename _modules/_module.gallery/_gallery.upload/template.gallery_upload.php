<?
function gallery_upload($val, $data)
{
	if ($val == 'upload')	return galleryUploadForm(getValue('folder'));
	if ($val == 'document'){
		$db		= module('doc', $data);
		$folder	= $db->folder();
	}else{
		$folder	= $data;
	}
?>
<iframe src="<?= getURL('gallery_upload_files', "folder=".urlencode($folder))?>" allowtransparency="1" frameborder="0" width="100%"></iframe>
<? } ?>
<?
function galleryUploadForm($folder){
	setTemplate('form');
	modFileAction($folder, true);
	@list($name, $path) = each(getFiles("$folder/Title"));
?>
<style>
body{
	color:white;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
}
</style>
<? if ($name){ ?>
<p style="background:#006600; padding:6px 5px"><b>{$name}</b></p>
<? } ?>
<form action="<?= getURL('gallery_upload_files', "folder=".urlencode($folder))?>" method="post" enctype="multipart/form-data">
<div>Загрузить файл для обложки</div>
<div><input name="modFileUpload[Title][]" type="file" /></div>
<p><input type="submit" class="button" value="Установть обложку" /></p>
</form>
<? } ?>