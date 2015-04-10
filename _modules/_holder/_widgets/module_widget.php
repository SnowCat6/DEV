<?
function module_widget($fn, $data)
{
	list($fn, $val) = explode(':', $fn, 2);
	$fn	= getFn("widget_$fn");
	if ($fn) return $fn($val, $data);
}
?>