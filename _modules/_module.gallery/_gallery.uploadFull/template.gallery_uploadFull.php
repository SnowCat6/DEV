<?
function gallery_uploadFull($type, $data)
{
	$db		= module('doc', $data);
	$id		= (int)$db->id();
	
	$folder	= $db->folder($id);
	if (!$type) $type = 'Image';
	if (!access('write', "file:$folder/$type/")) return;
	
	module('script:fileUploadFull');
	$files	= getFiles("$folder/$type");
	$p		= str_replace(localRootPath, globalRootURL, "$folder/$type");
?>
<div class="imageUploadFullHolder">
<div class="imageUploadFull imageUploadFullPlace" rel="{$p}">
Нажмите сюда для загрузки файла или петеращите файлы сюда.
</div>
<table border="0" cellspacing="0" cellpadding="0" class="table imageUploadFullTable">
<tr>
    <th class="upload"><div class="imageUploadFull" rel="{$p}"><span class="ui-icon ui-icon-arrowthickstop-1-s"></span></div></th>
    <th width="100%">Название</th>
    <th>Размер</th>
    <th>Загружен</th>
</tr>
<? foreach($files as $fileName => $path){
	$p2		= str_replace(localRootPath, globalRootURL, $path);
?>
<tr>
    <td class="delete"><a href="#" rel="{$p2}">x</a></td>
    <td><a href="{$p2}" target="_new">{$fileName}</a></td>
    <td nowrap="nowrap"><?= round(filesize($path)/1024) ?>кб.</td>
    <td nowrap="nowrap"><?= date('d.m.Y H:i', filemtime($path))?></td>
</tr>
<? } ?>
</table>
</div>
<? } ?>
<? function style_fileUploadFull($val){ ?>
<style>
.imageUploadFullTable tr.delete td{
	text-decoration:line-through;
	background:red;
	color:black;
}
.imageUploadFullTable td.delete a{
	text-decoration:none;
}
.imageUploadFullTable .upload{
	padding:0;
}
.imageUploadFullTable .imageUploadFull{
	padding:5px;
}
.imageUploadFullTable .imageUploadFull:hover{
	background:green;
}
.imageUploadFullPlace{
	border-radius:10px;
	padding:10px;
	border: dashed 4px white;
	margin-top:10px;
	background:green;
	color:white;
}
</style>
<? } ?>
<? function script_fileUploadFull($val){ module('script:jq_ui'); ?>
<script>
$(function(){
	$(".imageUploadFull").fileUpload(function(event, responce)
	{
		var holder = $($(this).parents(".imageUploadFullHolder").find(".imageUploadFullTable"));
		for(var image in responce)
		{
			var prop = responce[image];
			if (prop['error']) continue;
			holder.find("a:contains('"+image+"')").parent().parent().remove();

			var size = Math.round(prop['size'] / 1024, 2);
			var date = prop['date'];
			var html = '<tr>';
			html += '<td class="delete"><a href="#" rel="'+prop['path']+'">x</a></td>';
			html += '<td><a href="'+prop['path']+'" target="_new">'+image+'</a></td>';
			html += '<td nowrap="nowrap">'+size+'Кб.</td>';
			html += '<td nowrap="nowrap">'+date+'</td>';
			html += '</tr>';
			holder.append(html);
		}
		$(document).trigger('jqReady');
	});
	$(document).on('ready jqReady', function()
	{
		$(".imageUploadFullTable td.delete a").on('click.delete',function(){
			$(this).parent().parent().addClass("delete")
			.fileDelete($(this).attr("rel"), function(){
				$(this).remove();
			});
			return false;
		});
	});
});
</script>
<? } ?>
