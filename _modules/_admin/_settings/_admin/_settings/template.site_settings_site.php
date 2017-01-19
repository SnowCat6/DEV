<? function site_settings_site($ini)
{
	$settings	= array();
	$settings['Использовать кеш']	= array(
		'name'	=> 'settings[:][useCache]',
		'value'	=> '1',
		'checked'	=> $ini[':']['useCache']
	);
	$settings['Пооверять изменения файлов']	= array(
		'name'	=> 'settings[:][checkCompileFiles]',
		'value'	=> '1',
		'checked'	=> $ini[':']['checkCompileFiles']
	);
	$settings['Использовать сжатие страниц']	= array(
		'name'	=> 'settings[:][compress]',
		'value'	=> 'gzip',
		'checked'	=> $ini[':']['compress']=='gzip'
	);
	$settings['Объеденять CSS файлы']	= array(
		'name'	=> 'settings[:][unionCSS]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['unionCSS']=='yes'
	);
	$settings['Объеденять JavaScript файлы']	= array(
		'name'	=> 'settings[:][unionJScript]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['unionJScript']=='yes'
	);
	$settings['Загружить JavaScript в конце']	= array(
		'name'	=> 'settings[:][scriptLoad]',
		'value'	=> 'end',
		'checked'	=> $ini[':']['scriptLoad']=='end'
	);
	$settings['Разрешить мобильны вид']	= array(
		'name'	=> 'settings[:][mobileView]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['mobileView']=='yes'
	);
	$settings['Оптимизировать PHP код']	= array(
		'name'	=> 'settings[:][optimizePHP]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['optimizePHP']=='yes'
	);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="50%" valign="top">{{admin:settingsMenu:admin.settings.site=$settings}}</td>
      <td width="50%" valign="top">{{admin:menu:admin.tools.siteTools}}</td>
    </tr>
  </tbody>
</table>
<? return '1-Настройки сайта'; } ?>