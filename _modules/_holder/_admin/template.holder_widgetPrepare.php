<?
function holder_widgetPrepare($val, $widget)
{
	$widget	= holderUpdateWidget($widget);
	$widget	= holderCompileConfig($widget);

	$update	= $widget['update'];
	if ($update) moduleEx($update, $widget);
	
	foreach(array('exec', 'delete', 'preview') as $action)
	{
		$e	= explode('=', $widget[$action], 2);
		if (!$e[0]) continue;
		
		$widget[":$action"]['code']	= holderReplace($e[0], $widget);
		$widget[":$action"]['data']	= $e[1]?holderMakeArg($e[1], $widget):$widget['data'];
	}
	return $widget;
}
function holderMakeArg($arg, $data)
{
	$res	= array();
	$arg	= explode(';', $arg);
	foreach($arg as $line)
	{
		if ($line[0] == '[' && $line[strlen($line)-1] == ']'){
			dataMerge($res, holderMakeValue($line, $data));
			continue;
		}
		
		$name	= $val = '';
		list($name, $val)	= explode(':', $line);
		if ($val){
			$name	= holderReplace($name, $data);
			dataMerge($res, holderSetValue($name, holderMakeValue($val, $data), $res));
		}else{
			dataMerge($res, holderMakeValue($name, $data));
		}
	}
	
	return $res;
}
function holderCompileConfig($data)
{
	$data['data']	= array();
	$config			= $data['config'] or array();
	foreach($config as $name => $cfg)
	{
		$val	= $cfg['value']?$cfg['value']:$cfg['default'];
		
		$val	= holderReplace($val, $data);
		$data	= holderSetValue($name, $val, $data);
	}
	return $data;
}
function holderUpdateWidget($widget)
{
	$rawWidget	= module("holderAdmin:findWidget", $widget);
	if (!$rawWidget) return $widget;

	$rawWidget['id']						= $widget['id'];
	$rawWidget['config']['note']['name']	= 'Комментарий';

	$config	= $widget['config'] or array();
	foreach($config as $name => $value)
	{
		if (!isset($rawWidget['config'][$name]))
		{
			$name	= $value['name'];
			if (!isset($rawWidget['config'][$name]))
				continue;
		}

		$rawWidget['config'][$name]['value']	= $value['value'];
	}
	return $rawWidget;
}
function holderMakeValue($val, $data)
{
	if ($val[0] != '[' || $val[strlen($val)-1] != ']')
		return holderReplace($val, $data);

	$name	= substr($val, 1, strlen($val)-2);
	$val	= array();
	foreach(explode('.', $name) as $n)
		$data	= &$data[$n];

	if (is_array($data))
		return $data;
	
	$ret	= array();
	setDataValues($ret, $data);
	return $ret;
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