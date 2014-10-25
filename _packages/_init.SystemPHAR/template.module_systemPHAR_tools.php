<? function module_systemPHAR_tools(&$val, &$settings)
{
	$ini	= getCacheValue('ini');
	$settings['PHAR для системных файлов']	= array(
		'name'	=> 'settings[:][parSystem]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['parSystem']=='yes'
	);
} ?>