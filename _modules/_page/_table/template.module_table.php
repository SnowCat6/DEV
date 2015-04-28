<?
function module_table($name, $data)
{
	if (!$name) $name = 'default';
	if (strpos($name, '/') === false)
		$name	= "tables/$name";

	if (access('write', "text:$name"))
		return module("tableAdmin:$name");

	$val	= module("read_get:$name");
	echo module("text:split|table", $val);
}
?>