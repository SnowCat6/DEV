<?
//	filelist
function admin_tabUpdate($filter, &$data)
{
	$d		= array();
	@list($filter, $template) = explode(':', $filter, 2);
	$modules= getCacheValue('templates');

	$ev = array('', '', $data);
	event("admin.tab.$filter", $ev);
	if ($ev[0] && $ev[1]) $modules[$ev[0]] = $ev[1];

	foreach($modules as $name => $path){
		if (!preg_match("#$filter#", $name)) continue;
		$ev = array($name, $path, $data);
		event("admin.tab.$name", $ev);
		event("admin.tab.$name:$template", $ev);
		if ($ev[0] && $ev[1]) $d[$ev[0]] = $ev[1];
	}

	$tabs = array();
	foreach($d as $file => $path)
	{
		$file .= '_update';
		include_once($path);
		if (function_exists($file)) $file($data);
	}
}
?>