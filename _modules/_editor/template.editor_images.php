<? function editor_images($val, $folder)
{
	$url	= getValue('fileImagesPath');
	if ($val=='ajax'){
		setTemplate('');
		$folder	= $url;
		if (!is_array($folder)) $folder= array($folder);
		foreach($folder as &$p1) $p1 = normalFilePath(localRootPath."/$p1");
	}else{
		if (!is_array($folder)) $folder= array($folder);
	}
	
	m('script:jq');
	m('script:jq_ui');
	m('script:fileUpload');
	
	m('styleLoad', 'css/editorImages.css');
	m('scriptLoad', 'script/editorImages.js');
	
	$f			= array();
	foreach($folder as $name => &$p2) $f[$name] = str_replace(localRootPath.'/', globalRootURL, $p2);
	$url		= makeQueryString($f, 'fileImagesPath');
?>
<link rel="stylesheet" type="text/css" href="css/editorImages.css">

<div class="editorImages">
<div rel="{$url}" class="editorImageReload" title="Нажмите для обновления">
    <span class="ui-icon ui-icon-refresh"></span>
    Изображения
</div>

<div class="editorImageHolder shadow">
<table cellpadding="0" cellspacing="0" width="100%">
<?
$name	= '';
foreach($folder as $p)
{
	$files	= getFiles($p, '(jpeg|jpg|png|gif)$');
	
	$name	= explode('/', $p);
	$name	= htmlspecialchars(end($name));
	$p3		= json_encode(array(
		'uploadFolder' => str_replace(localRootPath.'/',	globalRootURL, $p))
	);
?>
<tbody>
	<tr>
    <th colspan="2">
        <div class="editorImageUpload" rel="{$p3}">{$name}</div>
    </th>
    </tr>
<?	if (!$files){ ?>
   <tr><td colspan="2" class="noImage">Нет изображений</td></tr>
<? } ?>
<?    
	foreach($files as $name => &$path)
	{
		list($w, $h) = getimagesize($path);
		$size	= "$w x $h";		
		$p		= str_replace(localRootPath.'/',	globalRootURL, $path);
?>
    <tr>
        <td class="image"><a href="/{$path}" target="_blank">{$name}</a></td>
        <td class="size"><a href="#" rel="{$p}"><span>{$size}</span><del>удалить</del><b>вставить</b></a></td>
    </tr>
<? } ?>
</tbody>
<? } ?>
</table>
</div>
</div>
<? } ?>
