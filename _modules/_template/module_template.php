<? function module_template($val, &$data)
{
	list($fn, $template)	= explode(':', $val, 2);
	$fn	= getFn("template_$fn");
	if ($fn) return $fn($template, $data);
}
function template_compile($template, &$ctx){
	$fn	= getFn("template_$template");
	if (!$fn) return;
	
	ob_start();
	$fn($template, $ctx);
	$ctx	= ob_get_clean();
}
function template_get($filter, &$data)
{
	$filter		= "template_$filter";
	
	$result		= array();
	$templates	= getCacheValue('templates');
	foreach($templates as $name => $path)
	{
		if (!preg_match("#^$filter#", $name)) continue;
		if (preg_match('#^template_(compile|get)#', $name)) continue;
		$name			= substr($name, strlen('template_'));
		$result[$name]	= $path;
	}
	return $result;
}
?>