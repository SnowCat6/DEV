<? function editor_images($val, $folder)
{
	if ($val == 'delete')
	{
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
		
		$bTitle	= strpos($folder, '/Title') > 0;
		if ($bTitle) delTree($folder);
		makeDir($folder);

		$files		= $_FILES['imageFieldUpload'];
		foreach($files['name'] as $ix => $file)
		{
			$fileName	= makeFileName($file);
			$filePath	= "$folder/$fileName";
			unlinkFile($filePath);
			if ($ix) echo ', ';
			if (move_uploaded_file($files['tmp_name'][$ix], $filePath)){
				fileMode($filePath);
				echo "$fileName OK";
				if ($bTitle) break;
			}else{
				echo "$fileName FALSE";
			}
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
	
	m('script:jq_ui');
	m('script:editorImages');
	
	$editorName	= $val?$val:'doc[originalDocument]';
	$f	= array();
	foreach($folder as $name => &$p2) $f[$name] = str_replace(globalRootURL.'/'.images.'/',	'', globalRootURL."/$p2");
	$url= makeQueryString($f, 'fileImagesPath');
?>
<script>editorName = '{$editorName}';</script>
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
<tbody>
	<tr>
    <th colspan="2">{$name}
    <div class="editorImageUpload" rel="{$p3}"><span class="ui-icon ui-icon-arrowthickstop-1-s"></span></div></th>
    </tr>
<?	if (!$files){ ?>
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
</tbody>
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
	z-index:999;
	right:0;
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
	color:white;
	font-weight:normal;
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
.editorImageHolder .editorImageUpload{
	float:right;
	padding:6px;
	text-align:center;
	cursor:pointer;
	position:relative;
}
.editorImageUpload:hover{
	background:green;
}
.imageUploadForm{
	position:absolute;
	top: 0; left: 0;
	width:100%;	height:100%;
}
.imageFieldUpload{
	display:block;
	width:100%; height:100%;
	opacity: 0; filter:alpha(opacity: 0);
	cursor:pointer;
}
.editorImages .editorImageHolder .delete a{
	text-decoration:line-through;
	background:red;
	color:black;
}
</style>
<iframe name="imageUploadFrame" id="imageUploadFrame" style="display:none"></iframe>
<script>
var submitImageUpload = false;
$(function(){
	$(document).on("jqReady ready", function()
	{
		$(".editorImageHolder .image a").on("click.imageUpload", function(){
			
			var size = $(this).parent().parent().find(".size").text().split(" x ");
			var html = '<img src="' + $(this).attr("href") + '"' + 'width="' + size[0] + '"' + 'height="' + size[1] + '"' + '/>';

			var FCK = window.parent.FCKeditorAPI;
			var editorName = $($(".submitEditor").get(0)).attr("name");
			var oEditor = FCK?FCK.GetInstance(editorName):null;
			if (oEditor){
				oEditor.InsertHtml(html);
			}
			return false;
		});
		
		$(".editorImageHolder .size a").on("click.imageUpload", function()
		{
			var parent = $(this).parent().parent().addClass("delete");
			$(this).load('{{url:file_images_delete}}?fileImagesPath=' + $(this).attr("rel"), function(){
				var p = parent.parent();	// tbody
				parent.remove();
				if (p.find("tr").length > 1) return;
				$('<tr><td colspan="2" class="noImage">Нет изображений</td></tr>').appendTo(p);
			});
			return false;
		});
		
		$(".editorImageReload").on("click.imageUpload", function()
		{
			var r = $(this).parent();
			r.html('<div class="editorImageReload reload"><span />Обновление...');
			r.load('{{url:file_images}}?' + $(this).attr("rel"), function(text){
				r.replaceWith(text);
				$(document).trigger("jqReady");
			});
			return false;
		});
		
		$(".editorImageUpload").each(function()
		{
			$(this).find("form").remove();
			
			var form = $('<form action="{{url:file_images_upload}}" class="imageUploadForm" method="post" enctype="multipart/form-data" target="imageUploadFrame"></form>');
			$('<input type="hidden" name="fileImagesPath" />').val($(this).attr("rel")).appendTo(form);
			$('<input type="file" class="imageFieldUpload" name="imageFieldUpload[]" multiple />').appendTo(form);
			
			form.appendTo($(this))
			.change(function(){
				submitImageUpload = true;
				$(this).submit();
				submitImageUpload = false;
			});
		});
	});
	
	$("#imageUploadFrame").load(function(){
		$(".editorImageReload").click();
	});
});
</script>
<? } ?>