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
	if ($data && $tag){
		list($tag, $class)	= explode(':', $tag, 2);
		if ($class) $class = " class=\"$class\"";
		$data	= "<$tag$class>$data</$tag>";
	}
}
function text_date($val, &$data)
{
	return $data = m("date:$val", $data);
}
function text_count($val, &$data)
{
	return $data = $data?count($data):'';
}
function text_class($val, &$data)
{
	if (is_array($data)) $data = implode(' ', $data);
	if ($data) return $data = " class=\"$data\"";
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
function text_module($val, &$data)
{
	$data = module($val, $data);
}
function text_m($val, &$data)
{
	$data = m($val, $data);
}
?>