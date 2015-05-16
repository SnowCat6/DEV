<?
function module_text($fx, &$data)
{
	foreach(explode('|', $fx) as $fn)
	{
		$val	= '';
		list($fn, $val)	= explode(':', $fn, 2);

		$fn	= getFn("text_$fn");
		if ($fn) $fn($val, $data);
	}
	return $data;
}
function text_show($val, &$data)
{
	if (is_array($data)){
		foreach($data as $v) echo $v;
	}else echo $data;
}
function text_tag($tag, &$data)
{
	list($tag, $class)	= explode(':', $tag, 2);
	if ($data && $tag){
		if ($class) $class = " class=\"$class\"";
		$data	= "<$tag$class>$data</$tag>";
	}
}
function text_style($val, &$data)
{
	if (is_array($data))
		return $data = makeStyle($data);
}
function text_property($val, &$data)
{
	if (is_array($data))
		return $data = makeProperty($data);
}
function text_json($val, &$data)
{
	if (is_array($data))
		return $data = json_encode($data);
}
function text_implode($val, &$data)
{
	if (is_array($data))
		return $data = implode($val, $data);
}
?>