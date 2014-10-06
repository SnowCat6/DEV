<?
function gallery_small(&$val, &$data)
{
	m('script:scroll');
	m('script:lightbox');
	m('page:style', 'css/gallerySmall.css');

	@$files = getFiles($data['src']);

	@$id	= $data['id'];
	if ($id) $id = "[$id]";
	
	$mask	= $data['mask'];
	$size	= $data['size'];
	if (!is_array($size)){
		$size = explode('x', $size);
		if (count($size) < 2) $size = array(50, 50);
	}
	
	$title	= htmlspecialchars($data['title']);
	if ($title) $title = "title=\"$title\"";

	galleryUpload($data, 'Нажмите для загрузки галереи');

	 if (!$files) return;
?>
<link rel="stylesheet" type="text/css" href="css/gallerySmall.css">
<div class="scroll gallery small">
<table cellpadding="0" cellspacing="0"><tr>
<?
foreach($files as $path){
$path2	= imagePath2local($path);
?>
<td><a href="{$path2}" rel="lightbox{$id}"{!$title}><? $mask?displayThumbImageMask($path, $mask):displayThumbImage($path, $size)?></a></td>
<? } ?>
</tr></table>
</div>
<? } ?>