<?
function module_text($fx, $data)
{
	foreach(explode('|', $fx) as $fn)
	{
		$val	= '';
		list($fn, $val)	= explode(':', $fn, 2);

		$fn	= getFn("text_$fn");
		if ($fn) $fn($val, $data);
	}
	if (is_array($data)){
		foreach($data as $v) echo $v;
	}else echo $data;
}
function text_note($val, &$data){
	$data = makeNote($data, $val);
}
?>