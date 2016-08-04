<?
function gallery_upload($type, $data)
{
	$db		= module('doc', $data);
	$id		= (int)$db->id();
	$folder	= $db->folder();
	if (!$type) $type = 'Title';

	if (!access('write', "file:$folder/$type/")) return;
	
	module('script:jq_ui');
	module('script:fileUpload');
	$folder	= rtrim("$folder/$type", '/');
	$files	= getFiles($folder);
	list($name, $path) = each($files);
	
	$file	=  str_replace(localRootPath.'/', globalRootURL, $folder);
	$p		= json_encode(array('uploadFolder' =>$file));
	m('script:jq_ui');
	m('script:fileUpload');
?>
<script src="script/gallery.upload.js"></script>
<link rel="stylesheet" type="text/css" href="css/gallery.upload.css">

<div id="imageTitleHolder" class="<?= $name?'imageTitleLoaded':'imageTitleNotLoaded'?>">
<table width="100%" class="imageTitleLoaded" cellpadding="0" cellspacing="0">
<tr>
    <td width="100%">
        <div class="imageTitleUpload imageTitleName" rel="{$p}">
          <div>Обложка: <span>/{$file}/{$name}</span></div>
          <div>Нажмите для загрузки новой обложки или перетащите изображение сюда.</div>
        </div>
    </td>
    <td nowrap="nowrap">
		<a href="#" class="imageTitleDelete">удалить</a>
	</td>
</tr>
</table>
<div class="imageTitleUpload imageTitleNotLoaded" rel="{$p}">
  <p>Обложка не загружена. </p>
  <p>Нажмите для загрузки обложки или перетащите изображение сюда.</p>
</div>
</div>

<div class="imageTitleHolderImage" style="overflow:auto; max-height:600px">
{{file:image=src:$path}}
</div>

<? } ?>