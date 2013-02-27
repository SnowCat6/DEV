<?
function gallery_upload($val, $data)
{
		
	if ($val == 'upload')
		return galleryUploadForm($data[1], $data[2]);
		
	$db		= module('doc', $data);
	$id		= $db->id();
	$folder	= $db->folder();
	$action = "gallery_upload_document$id"."_$val";
?>
<iframe src="<?= getURL($action)?>" allowtransparency="1" frameborder="0" width="250" height="350"></iframe>
<? } ?>
<?
function galleryUploadForm($id, $type)
{
	$id	= (int)$id;
	if (!access('write', "doc:$id")) return;
	
	$db		= module('doc');
	$folder	= $db->folder($id);
	$action = "gallery_upload_document$id"."_$type";

	//	Загрузить или удалить файлы
	//	Если это обложка документа, то при загрузке удалить имеющиеся файлы
	if (modFileAction($folder, $type == 'Title')){
		module("doc:recompile:$id");
	}

	setTemplate('form');
	module('script:jq');
	@list($name, $path) = each(getFiles("$folder/$type"));
?>
<form action="<?= getURL($action)?>" method="post" enctype="multipart/form-data">
<div>Обложка</div>
<? if ($name){ ?>
<div style="background:#006600; padding:6px 5px"><input name="modFile[files][{$type}][]" type="checkbox" value="{$name}" />
<a href="{$path}" target="_new"><b>{$name}</b></a>
</div>
<? }else{ ?>
<div style="background:#900; padding:6px 5px"><b>не загружена</b></div>
<? } ?>
<div>Загрузить файл для обложки</div>
<div><input name="modFileUpload[{$type}][]" type="file" class="fileupload" /></div>
<p><input type="submit" name="modFile[delButton]" class="button w100" value="Установть обложку" /></p>
</form>
<script>
$(function(){
	$(".fileupload").change(function(){
		$("form").submit();
	});
});
</script>
<? } ?>