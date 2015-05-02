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
			'data.src'	=> array(
				'type'		=> 'url',
				'name'		=> 'Путь',
				'default'	=> '[id]'
			),
			'data.property.class'	=> array(
				'type'	=> 'url',
				'name'	=> 'class'
			),
			'data.size'	=> array(
				'type'	=> 'url',
				'name'	=> 'size (WxH)'
			),
			'data.mask'	=> array(
				'type'	=> 'url',
				'name'	=> 'mask file'
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
    <img src="design/galleryWidgetPreview.jpg" width="400" height="268" alt=""/>
<? } ?>