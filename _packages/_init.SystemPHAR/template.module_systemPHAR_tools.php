<? function module_systemPHAR_tools(&$val, &$settings)
{
	$bEnable	= extension_loaded("phar") &&	extension_loaded("zip");
	$ini		= getCacheValue('ini');
	$settings['PHAR для системных файлов']	= array(
		'name'	=> 'settings[:][parSystem]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['parSystem']=='yes',
		'disable'	=> $bEnable == false
	);
} ?>