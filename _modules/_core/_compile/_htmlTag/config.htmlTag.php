<?
include ("_class/class.tagCompile.php");

addEvent('page.compile:before',	'htmlTagCompile');
function module_htmlTagCompile($val, &$ev)
{
	//	Заменить HTML тег
	//	<module:имя_модуля var.name.array="value" /> или 
	//	<module:имя_модуля var_name="@">контент</<module:имя_модуля>
	//	<module:имя_модуля ?="значение" /> - вызывается только если имеется значение
	//	На вызов модуля с параметрами, значение @ заменяется на содержимое между тегами
	$thisPage	= &$ev['content'];
	
	$compiller	= new moduleTagCompile('module:|mod:');
	$thisPage	= $compiller->compile($thisPage);

	$compiller	= new widgetTagCompile('widget:');
	$thisPage	= $compiller->compile($thisPage);
}

/********************************/
class moduleTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx)
	{
		$name		= explode(':', $name, 2);
		$moduleName	= $name[1] . $props['+'];
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
			$d	= $val=='@'?str_replace('"', '\\"', $ctx):$val;
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
};
/********************************/
class widgetTagCompile extends tagCompile
{
	function onTagCompile($name, $props, $ctx)
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
		global $_CONFIG;
		$_CONFIG[':htmlParseEvent']	= &$cfg;
		
		$compiller	= new widgetCfgTagCompile('cfg:');
		$ctx	= $compiller->compile($ctx);
		
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
	function onTagCompile($name, $props, $ctx)
	{
		$name	= explode(':', $name, 2);
		$name	= $name[1];
		
		global $_CONFIG;
		$cfg	= &$_CONFIG[':htmlParseEvent'];
		if (!$props['name']) $props['name'] = $name;
		$cfg['config'][$name]	= $props;
	
		return '';
	}
};
?>