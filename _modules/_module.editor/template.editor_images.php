<? function editor_images($val, $folder)
{
	if ($val == 'delete'){
		setTemplate('');
		$filePath	= getValue('fileImagesPath');
		$filePath	= normalFilePath(images."/$filePath");
		if (canEditFile($filePath)){
			unlinkFile($filePath);
		}else{
			echo 'Error';
		}
		return;
	}

	if ($val == 'upload')
	{
		setTemplate('');
		
		$folder	= getValue('fileImagesPath');
		$folder	= normalFilePath(images."/$folder");

		$file		= $_FILES['imageFieldUpload'];
		$fileName	= makeFileName($file['name']);
		$filePath	= "$folder/$fileName";
		
		if (strpos($folder, '/Title') > 0) delTree($folder);
		makeDir($folder);
		unlinkFile($filePath);
		if (move_uploaded_file($file['tmp_name'], $filePath)){
			fileMode($filePath);
			echo 'OK';
		}else{
			echo 'Error';
		}
		return;
	}
	
	$url	= getValue('fileImagesPath');
	if ($val=='ajax'){
		setTemplate('');
		$folder	= $url;
		if (!is_array($folder)) $folder= array($folder);
		foreach($folder as &$p1) $p1 = normalFilePath(images."/$p1");
	}else{
		if (!is_array($folder)) $folder= array($folder);
	}
	
	m('script:editorImages');
	
	$f	= array();
	foreach($folder as $name => &$p2) $f[$name] = str_replace(globalRootURL.'/'.images.'/',	'', globalRootURL."/$p2");
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
	$p3		= str_replace(globalRootURL.'/'.images.'/',	'', globalRootURL."/$p");
?>
	<tr>
    <th colspan="2">{$name}
    <span class="editroImageUpload" rel="{$p3}">+</span></th>
    </tr>
<?
	if (!$files){
?>
   <tr><td colspan="2" class="noImage">Нет изображений</td></tr>
<? }
    
	foreach($files as $name => &$path)
	{
		list($w, $h) = getimagesize($path);
		if (!$w || !$h) continue;
		$size	= "$w x $h";		
		$p		= str_replace(globalRootURL.'/'.images.'/',	'', globalRootURL."/$path");
?>
<tr>
    <td class="image"><a href="/{$path}" target="_blank">{$name}</a></td>
    <td class="size"><a href="#" rel="{$p}"><span>{$size}</span></a></td>
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
	padding:0 0 0 5px;
	font-size:20px;
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
.editorImageHolder .size{
	text-align:right;
	padding-right:20px;
}
.editorImageHolder .size a:hover{
	background:red;
}
.editorImageHolder tr:hover .size a:hover:after{
	content: "удалить";
	color:white;
}
.editorImageHolder tr:hover .size a span{
	display:none;
}
.editorImageHolder tr:hover .size a:after{
	content: "вставить";
	color:white;
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
.editroImageUpload{
	float:right;
	display:block;
	width:26px;
	text-align:center;
	cursor:pointer;
	padding:0;
}
.imageUploadForm{
	display:block;
	position:absolute;
	top: 0; left: 0;
	width:100%;	height:100%;
}
.imageFieldUpload{
	width:100%; height:100%;
	opacity: 0; filter:alpha(opacity: 0);
}
</style>
<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>
<script>
$(function(){
	$(document).on("jqReady ready", function()
	{
		$(".editorImageHolder .image a").click(function(){
			
			var size = $(this).parent().parent().find(".size").text().split(" x ");
			var html = '<img src="' + $(this).attr("href") + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';
			
			var FCK = window.parent.FCKeditorAPI;
			var oEditor = FCK?FCK.GetInstance("doc[originalDocument]"):null;
			if (oEditor){
				oEditor.InsertHtml(html);
			}
			return false;
		});
		
		$(".editorImageHolder .size a").click(function(){
			$(this).load('{{url:file_images_delete}}?fileImagesPath=' + $(this).attr("rel"), function(){
				$(".editorImageReload").click();
			});
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
		
		$(".editroImageUpload").each(function()
		{
			$(this).css({"position": "relative"});
			var form = $('<form action="{{url:file_images_upload}}" class="imageUploadForm" method="post" enctype="multipart/form-data" target="imageUploadFrame"></form>');
			$('<input type="hidden" name="fileImagesPath" />').val($(this).attr("rel")).appendTo(form);
			$('<input type="file" class="imageFieldUpload" name="imageFieldUpload" />').appendTo(form);
			form.appendTo($(this)).change(function(){
				$(this).submit();
			});
		});
	});
	
	$("#imageUploadFrame").load(function(){
		$(".editorImageReload").click();
	});
});
</script>
<? } ?>