<?
function module_preview(&$fn, &$data)
{
	//	Disable statistic
	define('statPages', true);
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn	= getFn("preview_$fn");
	if ($fn) return $fn($val, $data);
}
?>
