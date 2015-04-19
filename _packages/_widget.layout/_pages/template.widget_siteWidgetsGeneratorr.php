<?
function widget_siteWidgetsGeneratorr_config($val, &$widgets)
{
	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> 'Страница сайта',
		'title'		=> 'Формат страницы сайта',
		'exec'		=> 'widget:sitePage:[id]',
		'update'	=> 'widget:sitePageUpdate',
		'config'	=> array
		(
			'Ширина'	=> array(
				'name'		=> 'data.width',
				'default'	=> '1100px'
			),
			'Фон'	=> array(
				'name'	=> 'data.style.background',
				'type'	=> 'color',
				'default'	=> ''
			),
			'Тень'	=> array(
				'name'	=> 'data.options.shadow',
				'type'	=> 'checkbox',
				'default'	=> '1'
			),
			'Отступы'	=> array(
				'name'		=> 'data.style.padding',
				'default'	=> '0px 20px'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> '2 колонки левая',
		'title'		=> 'Двух колоночный формат ',
		'exec'		=> 'widget:siteLayout2:[id]',
		'config'	=> array
		(
			'Ширина левая'	=> array(
				'name'		=> 'data.widthLeft',
				'default'	=> '250px'
			),
			'Отступ'	=> array(
				'name'		=> 'data.padding',
				'default'	=> '20px'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> '2 колонки правая',
		'title'		=> 'Двух колоночный формат ',
		'exec'		=> 'widget:siteLayout2Right:[id]',
		'config'	=> array
		(
			'Ширина правая'	=> array(
				'name'		=> 'data.widthRight',
				'default'	=> '250px'
			),
			'Отступ'	=> array(
				'name'		=> 'data.padding',
				'default'	=> '20px'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);

	$widgets[]		=	array(
		'category'	=> 'Макет',
		'name'		=> '3 колонки',
		'title'		=> 'Трех колоночный формат ',
		'exec'		=> 'widget:siteLayout3:[id]',
		'config'	=> array
		(
			'Ширина левая'	=> array(
				'name'		=> 'data.widthLeft',
				'default'	=> '250px'
			),
			'Ширина правая'	=> array(
				'name'		=> 'data.widthRight',
				'default'	=> '250px'
			),
			'Отступ'	=> array(
				'name'		=> 'data.padding',
				'default'	=> '20px'
			),
			'class'	=> array(
				'name'		=> 'data.class',
				'default'	=> ''
			)
		)
	);
}
?>

<?
//	+function widget_sitePageUpdate
function widget_sitePageUpdate($id, &$widget)
{
	$data	= $widget['data'];
	
	$style	= array();
	$class	= array();
	
	foreach($widget['data']['style'] as $name=>$val){
		$style[$name]	= $val;
	}
	$style['width']		= $data['width'];
	$style['margin']	= 'auto';
	
	if ($data['class'])
		$class[$data['class']]	= $data['class'];

	if ($data['options']['shadow'])
		$class['shadow']	= 'shadow';
	
	if ($class){
		$class	= implode(' ', $class);
		$class	= "class=\"$class\"";
	}else $class = '';
	$widget['data'][':class']	= $class;

	$style	= makeStyle($style);
	$widget['data'][':style']	= $style;
}
?>