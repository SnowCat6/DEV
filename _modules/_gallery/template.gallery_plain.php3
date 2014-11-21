<?
function  gallery_plain($val, &$data)
{
	galleryUpload($data);
	
	$files	= gallery_files($val, $data['src']);
	if (!$files) return;
	
	//	Получить параметры
	$mask	= $data['mask'];
	$size	= $data['size'];
	if (!$size) $size = array(150, 150);
	
	$id	= $data['id'];
	if ($id) $id = "[$id]";

	module('script:lightbox');
?>
<link rel="stylesheet" type="text/css" href="css/gallery.css"/>
<div class="gallery flat">
<?
$ix	= 0;
foreach($files as $path =>$v){
//	if ($data['cols'] && ($ix%$cols)==0) echo '</div><div>';
	++$ix;
	$menu	= imageAdminMenu($path);
	$url	= $v['path'];
?>
<a href="{!$url}"rel="lightbox{$id}" class="galleryImage">
{{file:image=src:$path;mask:$mask;size:$size;adminMenu:$menu;property.title:$v[name];}}
</a>
<? if ($v['name'] || $v['comment']){ ?>
    <div class="imageContent">
        <h3>{$v[name]}</h3>
        <div>{$v[comment]}</div>
    </div>
<? } ?>
<? } ?>
</div>
<? } ?>