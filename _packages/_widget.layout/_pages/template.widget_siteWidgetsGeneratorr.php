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
			'data.width'	=> array(
				'name'		=> 'Ширина',
				'default'	=> '1100px'
			),
			'data.style.background'	=> array(
				'name'	=> 'Фон',
				'type'	=> 'color',
				'default'	=> ''
			),
			'data.options.shadow'	=> array(
				'name'	=> 'Тень',
				'type'	=> 'checkbox',
				'default'	=> '1'
			),
			'data.style.padding'	=> array(
				'name'		=> 'Отступы',
				'default'	=> '0px 20px'
			),
			'data.class'	=> array(
				'name'		=> 'class',
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
			'data.widthLeft'	=> array(
				'name'		=> 'Ширина левая',
				'default'	=> '250px'
			),
			'data.padding'	=> array(
				'name'		=> 'Отступ',
				'default'	=> '20px'
			),
			'data.class'	=> array(
				'name'		=> 'class',
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
			'data.widthRight'	=> array(
				'name'		=> 'Ширина правая',
				'default'	=> '250px'
			),
			'data.padding'	=> array(
				'name'		=> 'Отступ',
				'default'	=> '20px'
			),
			'data.class'	=> array(
				'name'		=> 'class',
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
			'data.widthLeft'	=> array(
				'name'		=> 'Ширина левая',
				'default'	=> '250px'
			),
			'data.widthRight'	=> array(
				'name'		=> 'Ширина правая',
				'default'	=> '250px'
			),
			'data.padding'	=> array(
				'name'		=> 'Отступ',
				'default'	=> '20px'
			),
			'data.class'	=> array(
				'name'		=> 'class',
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