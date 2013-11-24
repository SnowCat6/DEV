<?
function gallery_uploadFull($type, $data)
{
	$db		= module('doc', $data);
	$id		= (int)$db->id();
	if (!access('write', "doc:$id")) return;
	
	$folder	= $db->folder($id);
	if (!$type) $type = 'Image';
	
	module('script:fileUploadFull');
	$files	= getFiles("$folder/$type");
	$p		= str_replace(localRootPath, globalRootURL, "$folder/$type");
?>
<table border="0" cellspacing="0" cellpadding="0" class="table imageUploadFullTable">
<tr>
    <th class="upload"><div class="imageUploadFull"><span class="ui-icon ui-icon-arrowthickstop-1-s" rel="{$p}"></span></div></th>
    <th width="100%">Название</th>
    <th>Размер</th>
    <th>Загружен</th>
</tr>
<? foreach($files as $fileName => $path){
	$p		= str_replace(localRootPath, globalRootURL, $path);
?>
<tr>
    <td class="delete"><a href="#" rel="{$p}">x</a></td>
    <td><a href="{$p}" target="_new">{$fileName}</a></td>
    <td nowrap="nowrap"><?= round(filesize($path)/1024) ?>кб.</td>
    <td nowrap="nowrap"><?= date('d.m.Y H:i', filemtime($path))?></td>
</tr>
<? } ?>
</table>
<? } ?>
<? function script_fileUploadFull($val){ module('script:jq_ui'); ?>
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
</style>
<script>
$(function(){
	$(".imageUploadFull").fileUpload(function(event, responce)
	{
		var holder = $(this).parent().parent().parent();
		for(var image in responce)
		{
			var filePath = responce[image];
			var html = '<tr>';
			html += '<td class="delete"><a href="#" rel="'+filePath+'">x</a></td>';
			html += '<td><a href="'+filePath+'" target="_new">'+image+'</a></td>';
			html += '<td></td>';
			html += '<td></td>';
			html += '</tr>';
			holder.append(html);
		}
		$(document).trigger('jqReady');
	});
	$(document).on('load jqReady', function()
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
