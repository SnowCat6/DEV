<? function admin_staticFilesCompress(&$val, &$settings)
{
	$gini	= getGlobalCacheValue('ini');
	$settings['Статическое сжатие файлов']	= array(
		'name'	=> 'globalSettings[:][staticCompress]',
		'value'	=> 'yes',
		'checked'	=> $gini[':']['staticCompress']=='yes'
	);
} ?>