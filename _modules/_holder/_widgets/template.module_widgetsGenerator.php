<?
function module_widgetsGenerator($val, &$widgets)
{
	$modules= getCacheValue('templates');
	foreach($modules as $name => $path)
	{
		if (!preg_match("#^widget_#", $name)) continue;
		
		$fn			= getFn($name);
		$fnConfig	= getFn($name . "_config");
		if ($fnConfig) $fnConfig($val, $widgets);
	}
}
?>