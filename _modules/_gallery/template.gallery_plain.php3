<?
function gallery_plain_before($val, &$data){
	module('script:lightbox');
	module('page:style', 'css/gallery.css');
}
function  gallery_plain($val, &$data)
{
	//	Получить параметры
	$mask	= $data['mask'];
	$size	= $data['size'];
	if (!$size) $size = array(150, 150);
	
	$id	= $data['id'];
	if ($id) $id = "[$id]";

	$files	= gallery_files($val, $data['src']);
	//	Создать табличку
	$row	= 0;
	$cols	= $data['cols']?$data['cols']:4;
	if (count($files) < $cols) $cols = count($files);
	$percent= ' width="' . floor(100/$cols) . '%"';
	
	galleryUpload($data);
	//	Событие для добавления обработки галлереи
?>
<link rel="stylesheet" type="text/css" href="css/gallery.css"/>
<div class="gallery flat">
<?
$ix	= 0;
foreach($files as $path =>$v){
//	if ($data['cols'] && ($ix%$cols)==0) echo '</div><div>';
	++$ix;
	$menu	= imageAdminMenu($path);
?>
<? imageBeginAdmin($menu) ?>
<a href="{$v[path]}"rel="lightbox{$id}" class="galleryImage">
    <? $mask?displayThumbImageMask($path, $mask, '', $v['name']):displayThumbImage($path, $size, '', $v['name'])?>
<? if ($v['name'] || $v['comment']){ ?>
    <div class="imageContent">
        <h3>{$v[name]}</h3>
        <div>{$v[comment]}</div>
    </div>
<? } ?>
</a>
<? imageEndAdmin() ?>
<? } ?>
</div>
<? } ?>