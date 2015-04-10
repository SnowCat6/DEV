<?
function widget_gallery_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Фотогалерея',
		'module'	=> 'widget:gallery',
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
	mkDir($data['src']);
	module("gallery", $data);
}
?>