<? function editor_images($val, $folder)
{
	m('script:editorImages');
	$url	= getValue('fileImagesPath');
	if ($url && $val=='ajax'){
		setTemplate('');
		$folder	= $url;
		if (!is_array($folder)) $folder= array($folder);
		foreach($folder as &$p1) $p1 = normalFilePath(images."/$p1");
	}else{
		if (!is_array($folder)) $folder= array($folder);
	}
	
	
	$f	= $folder;
	foreach($f as &$p2) $p2 = str_replace(globalRootURL.'/'.images.'/',	'', globalRootURL."/$p2");;
	$url= makeQueryString($f, 'fileImagesPath');
?>
<div class="editorImages">
<div rel="{$url}" class="editorImageReload" title="Нажмите для обновления">
<span class="ui-icon ui-icon-refresh"></span>
Изображения</div>
<div class="editorImageHolder shadow">
<table cellpadding="0" cellspacing="0" width="100%">
<?

$name	= '';
foreach($folder as $p)
{
	$files	= getFiles($p, '(jpeg|jpg|png|gif)$');
	
	$name	= explode('/', $p);
	$name	= htmlspecialchars(end($name));
	echo "<tr><th colspan=\"2\">$name</th></tr>";
	if (!$files){ ?>
   <tr><td colspan="2" class="noImage">Нет изображений</td></tr>
    <? }
    
	foreach($files as $name => &$path)
	{
		list($w, $h) = getimagesize($path);
		if (!$w || !$h) continue;
		$size	= "$w x $h";		
?>
<tr>
    <td><a href="/{$path}" target="_blank">{$name}</a></td>
    <td class="size"><a href="/{$path}" target="_blank">{$size}</a></td>
</tr>
<? } ?>
<? } ?>
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
.editorImageHolder{
	min-width:200px;
}
.editorImages .editorImageHolder *{
	padding:0;
}
.editorImages a{
	text-decoration:none;
	display:block;
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
.editorImageHolder tr:hover a, .editorImages:hover .editorImageReload{
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
	text-align:right;
	padding-right:20px;
}
.editorImageHolder .noImage{
	padding:5px 10px;
}
.editorImageReload{
	padding:1px 10px;
	cursor:pointer;
	width:120px;
}
.editorImageReload span{
	zoom: 1;
	display:inline-block;
	*display: inline;
	height:16px;
}
.editorImageReload.reload{
	background:green !important;
	cursor:wait;
}
</style>
<script>
$(function(){
	$(document).on("jqReady ready", function(){
		$(".editorImageHolder a").click(function(){
			
			var size = $(this).parent().parent().find(".size").text().split(" x ");
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
			r.html('<div class="editorImageReload reload"><span />Обновление...');
			r.load('{{url:file_images}}?' + $(this).attr("rel"), function(text){
				r.replaceWith(text);
				$(document).trigger("jqReady");
			});
			return false;
		});
	});
	
});
</script>
<? } ?>