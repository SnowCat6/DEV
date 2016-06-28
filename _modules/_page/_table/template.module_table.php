<?
function module_table($name, $options)
{
	if (!$name) $name = 'default';
	if (strpos($name, '/') === false)
		$name	= "tables/$name";

	if (hasAccessRole('edit') && access('write', "text:$name"))
		return module("tableAdmin:$name", $options);

	$val	= module("read_get:$name");
	$fx		= $options['fx'];
	meta::begin(array(
		':tableSource'	=> $name
	));
	module("text:split|$fx|table|show", $val);
	meta::end();
}
?>