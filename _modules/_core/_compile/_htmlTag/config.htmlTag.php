<?
addEvent('page.compile:before',	'htmlTagCompile');
function module_htmlTagCompile($val, &$ev)
{
	global $_CONFIG;
	$_CONFIG[':htmlParseEvent'][]	= $ev;;
	//	Заменить HTML тег
	//	<module:имя_модуля var.name.array="value" /> или 
	//	<module:имя_модуля var_name="@">контент</<module:имя_модуля>
	//	<module:имя_модуля ?="значение" /> - вызывается только если имеется значение
	//	На вызов модуля с параметрами, значение @ заменяется на содержимое между тегами
	$thisPage	= &$ev['content'];
	$thisPage	= preg_replace_callback('#<(module:([^>\s]+))\b([^>]*)/>#sm', 			'fnHtmlTagCompile', $thisPage);
	$thisPage	= preg_replace_callback('#<(module:([^>\s]+))\b([^>]*)>(.*?)</\1>#sm',	'fnHtmlTagCompile', $thisPage);

//	$thisPage	= preg_replace_callback('#<(widget)\b([^>]*)>(.*?)</\1>#sm', 		'fnHtmlWidgetCompile', $thisPage);
	
	array_pop($_CONFIG[':htmlParseEvent']);
}
function fnHtmlTagCompile($val)
{
	$name	= $val[2];
	$prop	= $val[3];
	$ctx	= $val[4];
	
	$props	= parseHtmlProperty($prop);
	
	$moduleName	= $name . $props['+'];
	$props['+']	= '';
	
	$choose		= $props['?'];
	$props['?']	= '';
	if ($choose){
		$choose = "if (\"$choose\")";
	}
	
	$data	= array();
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
		$code	= 'array(' . implode(',', $data) . ')';
		$code	= "module(\"$moduleName\", $code)";
	}else{
		$code	= "module(\"$moduleName\")";
	}
	
	if ($choose) return "<? $choose $code ?>";
	
	return "<? $code ?>";
}
function fnHtmlWidgetCompile($val)
{
	global $_CONFIG;

	$prop	= $val[2];
	$ctx	= $val[3];

	$props	= parseHtmlProperty($prop);
	if (!$props) return;
	
	$ix		= count($_CONFIG[':htmlParseEvent']);
	$e		= $_CONFIG[':htmlParseEvent'][$ix];
	$ev		= array(
		'source'	=> $e['source'],
		'content'	=> &$ctx
	);
	event('page.compile', $ev);
	
	$cfg	= array();
	foreach($props as $name => $val)
	{
		$d	= &$cfg;
		foreach(explode('.', $name) as $n) $d = &$d[$n];
		$d	= $val;
	}
	
	return $ctx;
}
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