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
function text_style($val, &$data)
{
	return $data = makeStyle($data);
}
function text_property($val, &$data)
{
	return $data = makeProperty($data);
}
?>