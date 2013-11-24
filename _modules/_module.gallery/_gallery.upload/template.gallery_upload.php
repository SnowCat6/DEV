<?
function gallery_upload($type, $data)
{
	$db		= module('doc', $data);
	$id		= (int)$db->id();
	$folder	= $db->folder();
	if (!$type) $type = 'Title';

	if (!access('write', "doc:$id")) return;
	
	module('script:fileUpload');
	$folder	= rtrim("$folder/$type", '/');
	@list($name, $path) = each(getFiles($folder));
	$p		= str_replace(localRootPath.'/', globalRootURL, $folder);
?>
<div id="imageTitleHolder" class="<?= $name?'imageTitleLoaded':'imageTitleNotLoaded'?>">
<table width="100%" class="imageTitleLoaded" cellpadding="0" cellspacing="0">
<tr>
    <td width="100%">
        <div class="imageTitleUpload imageTitleName"><span>/{$p}/{$name}</span> - нажмите для загрузки новой картинки</div>
    </td>
    <td nowrap="nowrap">
		<a href="#" class="imageTitleDelete">удалить картинку</a>
	</td>
</tr>
</table>
<div class="imageTitleUpload imageTitleNotLoaded"><b>Обложка не загружена, нажмите для загрузки файла</b></div>
</div>

<div class="imageTitleHolderImage" style="overflow:auto; max-height:600px"><? displayImage($path)?></div>
<style>
.imageTitleLoaded .imageTitleNotLoaded, .imageTitleNotLoaded .imageTitleLoaded{
	display:none;
}
.imageTitleNotLoaded{
	background:#900;
	padding:2px 5px;
}
.imageTitleName{
	padding:2px 5px;
}
.imageTitleLoaded{
	background:#006600;
}
.imageTitleHolder{
	text-align:center;
}
</style>
<script>
$(function(){
	$(".imageTitleUpload").fileUpload("{$p}", function(event, responce){
		for(var image in responce){
			var fileName = responce[image];
			$(".imageTitleHolderImage").html('<img src="' + fileName + '" />');
			$(".imageTitleName span").text(fileName);
			$("#imageTitleHolder").attr("class", "imageTitleLoaded");
			break;
		}
	});
	$(".imageTitleDelete").click(function(){
		var fileName = $(this).parent().parent().find(".imageTitleName span").text();
		$(this).fileDelete(fileName, function(event, responce){
			$(".imageTitleHolderImage").html('');
			$("#imageTitleHolder").attr("class", "imageTitleNotLoaded");
		});
		return false;
	});
});
</script>
<? } ?>