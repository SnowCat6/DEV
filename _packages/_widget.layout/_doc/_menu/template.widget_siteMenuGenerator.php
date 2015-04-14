<? function widget_siteMenuGenerator_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Навигация',
		'name'		=> 'Вертикальное меню',
		'title'		=> 'Одноуровневое меню',
		'exec'		=> 'widget:siteMenu:[id]',
		'delete'	=> 'widget:siteMenuDelete:[id]',
		'config'	=> array
		(
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Навигация',
		'name'		=> 'Горизонтальное меню',
		'title'		=> 'Одноуровневое меню',
		'exec'		=> 'widget:siteMenuInline:[id]',
		'delete'	=> 'widget:siteMenuDelete:[id]',
		'config'	=> array
		(
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);
}?>

<?
//	+function widget_siteMenuDelete
function widget_siteMenuDelete($id, $data){
	m("prop:unset", array(
		'!place' => $id
	));
	echo $id; die;
}?>