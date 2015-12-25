<?
//	+function module_iniIO_set
function module_iniIO_set($val, $data)
{
	$key	= $data['key'];
	if (!preg_match('#^ini\.(.*)#', $key, $val)) return;

	$action	= $data['options'];
	$action	= $action['action'];
	
	$path	= $val[1];
	$ini	= getCacheValue('ini');
	systemIO::set_data($ini, $path, $data['value'], $action);
	setIniValues($ini);
}
//	+function module_iniIO_get
function module_iniIO_get($val, $data)
{
	$key	= $data['key'];
	if (!preg_match('#^ini\.(.*)#', $key, $val)) return;
	
	$path	= $val[1];
	$ini	= getCacheValue('ini');
	$data['value']	= systemIO::get_data($ini, $path);
}
?>
