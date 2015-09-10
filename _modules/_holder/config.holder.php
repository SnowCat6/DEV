<?
addUrl('admin_holderEdit',			'holderAdmin:uiEdit');
addUrl('admin_holderWidgetEdit',	'holderAdmin:uiWidgetEdit');
addUrl('admin_holderMode',			'holderAdmin:uiMode');

addUrl('admin_widgetLoad',			'holderAdmin:widgetLoad');
addUrl('ajax_widget_add',			'holderAdmin:ajaxWidgetAdd');
addUrl('ajax_widget_sort',			'holderAdmin:ajaxWidgetSort');
//	Инстументы для административной панели
addEvent('admin.tools.settings',	'holderAdmin:tools');

addAccess('holder:(.*)',	'holderAccess');

addEvent('page.compile:before',	'htmlWidgetCompile');
function module_htmlWidgetCompile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	$compiller	= new widgetTagCompile('widget:');
	$thisPage	= $compiller->compile($thisPage);
}

/********************************/
class widgetTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$name	= explode(':', $name, 2);
		$name	= $name[1];
		if (!$props['className']) 	$props['className']	= $name;
		if (!$props['name']) 		$props['name']		= $name;
		if (!$props['category'])	$props['category']	= 'Widgets';
		if (!$props['exec']) 		$props['exec']		= "widget:$name:[id]=[data]";
		if (!$props['update'])		$props['update']	= "widgetGenerator:update:[id]";
		if (!$props['delete'])		$props['delete']	= "widgetGenerator:delete:[id]=[data]";
		if (!$props['preview'])		$props['preview']	= "widgetGenerator:preview:[id]=image:design/preview_$name.jpg";
		
		$cfg	= array();
		foreach($props as $propertyName => $val){
			$d	= &$cfg;
			foreach(explode('.', $propertyName) as $n) $d = &$d[$n];
			$d	= $val;
		}
		
		$compiller	= new widgetCfgTagCompile('cfg:');
		$ctx	= $compiller->compile($ctx, array('cfg' => &$cfg));
		
		$code	= makeParseVar($cfg);
		$code	= 'array(' . implode(',', $code) . ')';
		$code	= "<?
		// +function widget_$name"."_config
		function widget_$name" . "_config(\$val, &\$widgets) {
		\$widgets[] = $code;\r\n}
		?>
		";
	
		return $code . trim($ctx);
	}
};
/********************************/
class widgetCfgTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx, $options)
	{
		$name	= explode(':', $name, 2);
		$name	= $name[1];
		
		if (!$props['name']) $props['name'] = $name;
		$options['cfg']['config'][$name]	= $props;
	
		return '';
	}
};
?>