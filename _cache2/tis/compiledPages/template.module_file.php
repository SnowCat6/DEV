<?
function module_file($val, $data=''){
	//	Попробовать загрузить дополнительный модуль
	@list($val, $v)=explode(':', $val, 2);
	$fn = getFn("file_$val");
	if ($fn) return $fn($v, $data);
}
?>