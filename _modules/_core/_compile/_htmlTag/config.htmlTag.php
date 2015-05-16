<?
addEvent('page.compile:before',	'htmlTagCompile');
function module_htmlTagCompile($val, &$ev)
{
	//	Заменить HTML тег
	//	<module:имя_модуля var.name.array="value" /> или 
	//	<module:имя_модуля var_name="@">контент</<module:имя_модуля>
	//	<module:имя_модуля ?="значение" /> - вызывается только если имеется значение
	//	На вызов модуля с параметрами, значение @ заменяется на содержимое между тегами
	$thisPage	= &$ev['content'];
	$thisPage	= preg_replace_callback('#<((module|mod):([^>\s]+))\b([^>]*)/>#sm', 			'fnHtmlTagCompile', $thisPage);
	$thisPage	= preg_replace_callback('#<((module|mod):([^>\s]+))\b([^>]*)>(.*?)</\1>#sm',	'fnHtmlTagCompile', $thisPage);

	$thisPage	= preg_replace_callback('#<(widget:([^>\s]+))\b([^>]*)>(.*?)</\1>#sm', 'fnHtmlWidgetCompile', $thisPage);
}

////////////////////////////////////////////
function fnHtmlTagCompile($val)
{
	$name	= $val[3];
	$prop	= $val[4];
	$ctx	= str_replace('"', '\\"', $val[5]);
	
	$props	= parseHtmlProperty($prop);
	
	$moduleName	= $name . $props['+'];
	$props['+']	= '';
	
	$choose		= $props['?'];
	$props['?']	= '';
	if ($choose){
		$choose = "if (\"$choose\")";
	}
	
	$data	= $props['@'] or array();
	$props['@']	= '';
	
	foreach($props as $name => $val)
	{
		if (!$val) continue;
		//	Контент если есть между тегами
		
		$d	= &$data;
		foreach(explode('.', $name) as $n) $d = &$d[$n];
		$d	= $val == '@'?$ctx:$val;
	}
	$data	= makeParseVar($data);

	if ($data){
		if (is_array($data)){
			$code	= 'array(' . implode(',', $data) . ')';
			$code	= "module(\"$moduleName\", $code)";
		}else{
			$code	= "module(\"$moduleName\", $data)";
		}
	}else{
		$code	= "module(\"$moduleName\")";
	}
	
	if ($choose) return "<? $choose $code ?>";
	
	return "<? $code ?>";
}

////////////////////////////////////////////
function fnHtmlWidgetCompile($val)
{
	global $_CONFIG;

	$name	= $val[2];
	$prop	= $val[3];
	$ctx	= $val[4];

	$props	= parseHtmlProperty($prop);
	if (!$props['className']) 	$props['className']		= $name;
	if (!$props['name']) 	$props['name']		= $name;
	if (!$props['category'])$props['category']	= 'Widgets';
	if (!$props['exec']) 	$props['exec']		= "widget:$name:[id]=[data]";
	if (!$props['update'])	$props['update']	= "widgetGenerator:update:[id]";
	if (!$props['delete'])	$props['delete']	= "widgetGenerator:delete:[id]=[data]";
	if (!$props['preview'])	$props['preview']	= "widgetGenerator:preview:[id]=image:design/preview_$name.jpg";
	
	$cfg	= array();
	foreach($props as $propertyName => $val){
		$d	= &$cfg;
		foreach(explode('.', $propertyName) as $n) $d = &$d[$n];
		$d	= $val;
	}
	$_CONFIG[':htmlParseEvent']	= &$cfg;
	
	$ctx	= preg_replace_callback('#<(cfg:([^>\s]+))\b([^>]*)/>#sm', 			'fnHtmlWidgetCfg', $ctx);
	$ctx	= preg_replace_callback('#<(cfg:([^>\s]+))\b([^>]*)>(.*?)</\1>#sm', 'fnHtmlWidgetCfg', $ctx);
	
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
function fnHtmlWidgetCfg($val)
{
	global $_CONFIG;
	
	$name	= $val[2];
	$prop	= $val[3];
	$ctx	= $val[4];

	$cfg	= &$_CONFIG[':htmlParseEvent'];
	$props	= parseHtmlProperty($prop);
	if (!$props['name']) $props['name'] = $name;
	$cfg['config'][$name]	= $props;

	return '';
}

////////////////////////////////////////////
function parseHtmlProperty($property)
{
	$pattern= '#([^\s=\"\']+)\s*(=\s*([\"\'])(.*?)\3)#';
	preg_match_all($pattern, $property, $var);

	$props	= array();
	foreach($var[1] as $ix=>$name)
		$props[$name]	= $var[4][$ix];
		
	return $props;
//	$x	= new SimpleXMLElement("<element $property />");
//	return current($x->attributes());
}
?>