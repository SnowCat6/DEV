<?
function widget_gallery_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Фотогалерея',
		'title'		=> 'Размещение фотографий',
		'exec'		=> 'widget:gallery',
		'delete'	=> 'widget:gallery:unlink',
		'preview'	=> 'widget:gallery:preview',
		'config'	=> array
		(
			'Путь'	=> array(
				'type'		=> 'url',
				'name'		=> 'data.src',
				'default'	=> '[id]'
			),
			'class'	=> array(
				'type'		=> 'url',
				'name'		=> 'data.property.class'
			),
			'size (WxH)'	=> array(
				'type'		=> 'url',
				'name'		=> 'data.size'
			),
			'mask file'	=> array(
				'type'		=> 'url',
				'name'		=> 'data.mask'
			)
		)
	);
}
function widget_gallery($val, $data)
{
	$data['id']		= $data['src'];
	$data['src']	= images . "/$data[src]";
	
	switch($val){
	case 'unlink':
		return module("file:unlink", $data['src']);
	case 'preview':
		return widgetGalleryPreview($data);
	default:
		mkDir($data['src']);
		module("gallery", $data);
	}
}
function widgetGalleryPreview($data){ ?>
<div class="widgetGalleryPreview">
    <img src="design/galleryWidgetPreview.jpg" width="400" height="268" alt=""/>
</div>
<? } ?>