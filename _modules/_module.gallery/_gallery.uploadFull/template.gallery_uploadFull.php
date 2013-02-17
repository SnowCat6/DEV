<?
function gallery_uploadFull($type, $data)
{
	if ($type != 'upload') return uploadFullFrame($type, $data);

	$id		= $data[1];
	if (!access('write', "doc:$id")) return;
	
	$type	= $data[2];
	$db		= module('doc');
	$folder	= $db->folder($id);
	if (!$type) $type = 'Image';
	$action = "gallery_uploadFull_document$id"."_$type";
	
	//	Загрузить или удалить файлы
	//	Если это обложка документа, то при загрузке удалить имеющиеся файлы
	if (modFileAction($folder, $type == 'Title')){
		module("doc:recompile:$id");
	}

	setTemplate('form');
	module('script:jq');
	$files	= getFiles("$folder/$type");
?>
<link rel="stylesheet" type="text/css" href="../../_module.admin/admin.css"/>
<form action="{{getURL:$action}}" method="post" enctype="multipart/form-data" class="admin">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%" style="background:#060">
<input class="fileupload w100" style="height:34px" type="file" name="modFileUpload[{$type}][]" multiple="multiple">
    </td>
    <td>
<input name="Submit" type="submit" class="button" value="Выполнить" />
    </td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%">Название</th>
    <th>Размер</th>
    <th>Загружен</th>
</tr>
<? foreach($files as $fileName => $path){?>
<tr>
    <td><input name="modFile[delete][{$type}][]" type="checkbox" value="{$fileName}" /></td>
    <td><a href="{$path}" target="_new">{$fileName}</a></td>
    <td nowrap="nowrap"><?= round(filesize($path)/1024) ?>кб.</td>
    <td nowrap="nowrap"><?= date('d.m.Y H:i', filemtime($path))?></td>
</tr>
<? } ?>
</table>
</form>
<script>
$(function(){
	$(".fileupload").change(function(){
		$("form").submit();
	});
});
</script>
<? } ?>
<?
function uploadFullFrame($type, $data){
	$db		= module('doc', $data);
	$id		= $db->id();
	$folder	= $db->folder();
	$action = "gallery_uploadFull_document$id"."_$type";
?>
<div style="height:350px">
	<iframe src="<?= getURL($action)?>" allowtransparency="1" frameborder="0" width="100%" height="100%"></iframe>
</div>
<? } ?>