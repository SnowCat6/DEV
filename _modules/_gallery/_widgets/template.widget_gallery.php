<?
function widget_gallery_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Информация',
		'name'		=> 'Фотогалерея',
		'exec'		=> 'widget:gallery',
		'delete'	=> 'widget:gallery:unlink',
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
	
	if ($val == 'unlink'){
		module("file:unlink", $data['src']);
	}else{
		mkDir($data['src']);
		module("gallery", $data);
	}
}
?>