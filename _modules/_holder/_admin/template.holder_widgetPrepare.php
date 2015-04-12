<?
function holder_widgetPrepare($val, $widget)
{
	$widget	= holderUpdateWidget($widget);
	$widget	= holderCompileConfig($widget);
	
	foreach(array('exec', 'delete', 'preview') as $action)
	{
		$e	= explode('=', holderReplace($widget[$action], $widget), 2);
		if (!$e[0]) continue;
		
		$widget[":$action"]['code']	= $e[0];
		$widget[":$action"]['data']	= $e[1]?holderMakeArg($e[1]):$widget['data'];
	}
	return $widget;
}
function holderMakeArg($arg)
{
	$res	= array();
	$arg	= explode(';', $arg);
	foreach($arg as $line)
	{
		$name	= $val = '';
		list($name, $val)	= explode(':', $line);
		$res	= holderSetValue($name, $val, $res);
	}

	return $res;
}
function holderCompileConfig($data)
{
	$data['data']	= array();
	$config			= $data['config'];
	if (!is_array($config)) $config = array();
	
	foreach($config as $cfg)
	{
		$val	= $cfg['value']?$cfg['value']:$cfg['default'];
		$val	= holderReplace($val, $data);
		$data	= holderSetValue($cfg['name'], $val, $data);
	}
	return $data;
}
function holderUpdateWidget($widget)
{
	$rawWidget	= module("holderAdmin:findWidget", $widget);
	if (!$rawWidget) return $widget;

	$rawWidget['id']	= $widget['id'];
	$rawWidget['config']['Комментарий']['name']	= 'note';
	
	$cfg	= $widget['config'];
	if (!is_array($cfg)) $cfg = array();
	
	foreach($cfg as $name => $value)
	{
		if (!isset($rawWidget['config'][$name])) continue;
		$rawWidget['config'][$name]['value']	= $value['value'];
	}
	return $rawWidget;
}
function holderSetValue($name, $val, $data)
{
	if (!$name) return $data;
	
	$d	= &$data;
	foreach(explode('.', $name) as $n) $d	= &$d[$n];
	$d	= $val;

	return $data;
}
function holderReplace($val, $data)
{
	global $holderExecReplace;
	$holderExecReplace	= $data;

	return preg_replace_callback('#\[([^\]]+)\]#', 'fnHolderReplace', $val);
}
function fnHolderReplace($val)
{
	global $holderExecReplace;

	$p	= $holderExecReplace;

	foreach(explode('.', $val[1]) as $n)
		$p	= &$p[$n];
		
	return $p;
}

?>