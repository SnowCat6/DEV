<?
function module_feedback($fn, $data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("feedback_$fn");
	return $fn?$fn($val, &$data):NULL;
}
?>