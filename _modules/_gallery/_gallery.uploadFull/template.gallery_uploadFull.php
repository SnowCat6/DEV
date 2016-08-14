<?
function gallery_uploadFull($type, $data)
{
	$db		= module('doc', $data);
	$id		= (int)$db->id();
	
	$folder	= $db->folder($id);
	if (!$type) $type = 'Image';
	if (!access('write', "file:$folder/$type/")) return;
	
	m('script:fileUploadFull');
	$files	= getFiles("$folder/$type");
	$p		= json_encode(array(
		'uploadFolder' => str_replace(localRootPath, globalRootURL, "$folder/$type")
	));
	m('script:jq_ui');
	m('script:fileUpload');
?>
<link rel="stylesheet" type="text/css" href="css/gallery.upload.css">
<script src="script/gallery.upload.js"></script>

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

