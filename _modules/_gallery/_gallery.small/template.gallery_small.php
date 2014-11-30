<?
function gallery_small(&$val, &$data)
{
	m('script:scroll');
	m('script:lightbox');

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
<? foreach($files as $path){ ?>
<td>
{{file:image=src:$path;mask:$mask;size:$size;property.href:$path2;property.rel:lightbox$id;property.title:$title}}
</td>
<? } ?>
</tr></table>
</div>
<? } ?>