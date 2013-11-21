<? function editor_images($val, $folder)
{
	m('script:editorImages');
	$url	= getValue('fileImagesPath');
	if ($url && $val=='ajax'){
		setTemplate('');
		$folder	= $url;
	}
	$url	= makeQueryString($folder, 'fileImagesPath');
?>
<div class="editorImages">
<a href="<?= getURL('file_images', $url)?>" class="editorImageReload" title="Нажмите для обновления">
<span class="ui-icon ui-icon-refresh"></span>
Изображения</a>
<div class="editorImageHolder shadow">
<table cellpadding="0" cellspacing="0">
<?
if (!is_array($folder)) $folder= array($folder);

$name	= '';
foreach($folder as $p)
{
	$files	= getFiles($p, '(jpeg|jpg|png|gif)$');
	if (!$files) continue;
	
	$name	= explode('/', $p);
	$name	= htmlspecialchars(end($name));
	echo "<tr><th colspan=\"2\">$name</th></tr>";
	
	foreach($files as $name => &$path)
	{
		list($w, $h) = getimagesize($path);
		if (!$w || !$h) continue;
		$size	= "$w x $h";
?>
<tr>
    <td><a href="/{$path}" target="_blank">{$name}</a></td>
    <td class="size">{$size}</td>
</tr>
<? } ?>
<? } ?>
<? if (!$name){
	echo "<tr><th colspan=\"2\">Нет изображений</th></tr>";
}?>
</table>
</div>
</div>
<? } ?>
<? function script_editorImages(){
	m('script:jq');
?>
<style>
.editorImages{
	position:relative;
	white-space:nowrap;
}
.editorImages .editorImageHolder *{
	padding:0;
}
.editorImages a{
	text-decoration:none;
	padding:1px 5px;
	display:block;
	white-space:nowrap;
}
.editorImages .editorImageHolder th{
	background:#444;
	padding:2px 5px;
}
.editorImages .editorImageHolder a{
	padding:5px 10px;
	width:100%;
	color:#000;
}
.editorImages a:hover{
	background:#09F;
}
.editorImages .editorImageHolder{
	display:none;
	background:#FFF;
	color:#000;
	position:absolute;
	top:100%; 
	border:solid 1px #888;
	white-space:nowrap;
}
.editorImages:hover .editorImageHolder{
	display:block;
}
.editorImages .editorImageHolder .size{
	padding:5px 10px;
	text-align:right;
}
.editorImagesReload{
	background:green;
	padding:1px 5px;
}
.editorImageReload span{
	zoom: 1;
	display:inline-block;
	*display: inline;
}
</style>
<script>
$(function(){
	$(document).on("jqReady ready", function(){
		$(".editorImageHolder a").click(function(){
			
			var size = $(this).find("span").text().split(" x ");
			var html = '<img src="' + $(this).attr("href") + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';
			
			var FCK = window.parent.FCKeditorAPI;
			var oEditor = FCK?FCK.GetInstance("doc[originalDocument]"):null;
			if (oEditor){
				oEditor.InsertHtml(html);
			}
			return false;
		});
		$(".editorImageReload").click(function(){
			var r = $(this).parent();
			r.html('<div class="editorImagesReload">Обновление...');
			r.load($(this).attr("href"), function(text){
				r.replaceWith(text);
				$(document).trigger("jqReady");
			});
			return false;
		});
	});
	
});
</script>
<? } ?>