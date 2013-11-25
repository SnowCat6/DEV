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
        <div class="imageTitleUpload imageTitleName">
          <p>Обложка: <span>/{$p}/{$name}</span> </p>
          <p>Нажмите для загрузки новой обложки или перетащите изображение сюда.</p>
        </div>
    </td>
    <td nowrap="nowrap">
		<a href="#" class="imageTitleDelete">удалить</a>
	</td>
</tr>
</table>
<div class="imageTitleUpload imageTitleNotLoaded">
  <p>Обложка не загружена. </p>
  <p>Нажмите для загрузки обложки или перетащите изображение сюда.</p>
</div>
</div>

<div class="imageTitleHolderImage" style="overflow:auto; max-height:600px"><? displayImage($path)?></div>
<style>
.imageTitleLoaded .imageTitleNotLoaded, .imageTitleNotLoaded .imageTitleLoaded{
	display:none;
}
.imageTitleNotLoaded .imageTitleNotLoaded{
	background:#900;
	padding:0 10px;
	border-radius:10px;
	border:dashed 4px white;
}
.imageTitleName{
	padding:0 10px;
	background:#006600;
	border-radius:10px;
	border:dashed 4px white;
}
.imageTitleHolder{
	text-align:center;
}
.imageTitleDelete{
	background:red;
	color:white;
	display:block;
	padding:10px;
	border-radius:10px;
}
</style>
<script>
$(function(){
	$(".imageTitleUpload").fileUpload("{$p}", function(event, responce){
		for(var image in responce){
			var attr = responce[image];
			if (attr['error']) continue;
			
			var fileName = attr['path'];
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