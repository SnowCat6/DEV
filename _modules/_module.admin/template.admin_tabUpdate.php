<?
//	filelist
function admin_tabUpdate($filter, &$data)
{
	$d		= array();
	$modules= getCacheValue('templates');
	foreach($modules as $name => $path){
		if (!preg_match("#$filter#", $name)) continue;
		$d[$name] = $path;
	}
	
	$tabs = array();
	foreach($d as $file => $path)
	{
		ob_start();
		include_once($path);
		$file .= '_update';
		if (function_exists($file)) $file(&$data);
		$ctx = trim(ob_get_clean());
	}
}
?>