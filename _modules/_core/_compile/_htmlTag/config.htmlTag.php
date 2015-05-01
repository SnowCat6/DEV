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
	$thisPage	= preg_replace_callback('#<(module:([^<>\s]+))(.*)/>#', 'fnHtmlTagCompile', $thisPage);
	$thisPage	= preg_replace_callback('#<(module:([^<>\s]+))(.*)>(.*)</\1>#u', 'fnHtmlTagCompile', $thisPage);
}
function fnHtmlTagCompile($val)
{
	$name	= $val[2];
	$prop	= $val[3];
	$ctx	= $val[4];
	
	$props	= array();
	if (preg_match_all('#(\S+)=[\"\']?((?:.(?![\"\']?\s+(?:\S+)=|[>\"\']))+.)[\"\']?#', $prop, $tmpProps))
	{
		foreach($tmpProps[1] as $ix => $propName)
		{
			$val				= $tmpProps[2][$ix];
			if ($val == '@') $val = $ctx;
			if ($propName == '+') $props[$propName]	.= $val;
			else $props[$propName]	= $val;
		}
	}
	
	$moduleName	= $name . $props['+'];
	$props['+']	= '';
	
	$choose	= $props['?'];
	if ($choose){
		$choose = "if (\"$choose\")";
	}
	$props['?']	= '';
	
	$data	= array();
	foreach($props as $name => $val)
	{
		if (!$val) continue;
		//	Контент если есть между тегами
		
		$d	= &$data;
		foreach(explode('.', $name) as $n) $d = &$d[$n];
		$d	= $val;
	}
	$data	= makeParseVar($data);

	if ($data){
		//	new code
		$code	= 'array(' . implode(',', $data) . ')';
		$code	= "module(\"$moduleName\", $code)";
	}else{
		$code	= "module(\"$moduleName\")";
	}
	
	if ($choose) return "<? $choose $code ?>";
	
	return "<? $code ?>";
}
?>