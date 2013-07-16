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
<iframe src="<?= getURL($action)?>" allowtransparency="1" frameborder="0" width="100%" height="550"></iframe>
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
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td nowrap="nowrap">
<? if ($name){ ?>
<div style="background:#006600; padding:6px 5px">
<input name="modFile[files][<? if(isset($type)) echo htmlspecialchars($type) ?>][]" type="checkbox" value="<? if(isset($name)) echo htmlspecialchars($name) ?>" />
<a href="<? if(isset($path)) echo htmlspecialchars($path) ?>" target="_new"><b><? if(isset($name)) echo htmlspecialchars($name) ?></b></a>
</div>
<? }else{ ?>
<div style="background:#900; padding:6px 5px"><b>не загружена</b></div>
<? } ?>
    </td>
    <td align="right" valign="top" style="padding-left:10px">
<div><input name="modFileUpload[<? if(isset($type)) echo htmlspecialchars($type) ?>][]" type="file" class="fileupload w100" /></div>
</td>
  </tr>
</table>
<p><input type="submit" name="modFile[delButton]" class="button w100" value="Установть" /></p>
</form>
<? displayImage($path)?>
<script>
$(function(){
	$(".fileupload").change(function(){
		$("form").submit();
	});
});
</script>
<? } ?>