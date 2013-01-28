<?
//	module user
function module_debug(&$fn, &$data){
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("debug_$fn");
	return $fn?$fn($val, $data):NULL;
}
function debug_executeTime(){
	echo 'Время выполнения: ', round(getmicrotime() - sessionTimeStart, 3), ' сек.';
}
?>
