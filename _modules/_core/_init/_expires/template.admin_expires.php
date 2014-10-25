<? function admin_expires(&$val, &$settings)
{
	$gini	= getGlobalCacheValue('ini');
	$settings['Использовать время кеширования контента']	= array(
		'name'	=> 'globalSettings[:][useExpires]',
		'value'	=> 'yes',
		'checked'	=> $gini[':']['useExpires']=='yes'
	);
} ?>