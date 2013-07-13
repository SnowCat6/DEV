<? function module_cron($fn, &$data)
{
	list($fn, $val) = explode(':', $fn);
	$fn = getFn("cron_$fn");
	return $fn?$fn($val, $data):NULL;
}
function cron_synch($val, &$data){
	event('cron.synch', $data);
}
function cron_add($name, &$data){
	$crons = getCacheValue('cronWork');
	if (!is_array($crons)) $crons = array();
	$crons[$name] = $data;
	setCacheValue('cronWork', $crons);
}
?>