<?
function holder_widgetPrepare($val, $widget)
{

	$widget			= holderCompileConfig($widget);
	list($c, $d)	= explode('=', holderReplace($widget['module'], $widget), 2);
	
	$exec			= array();
	$exec['code']	= $c;
	$exec['data']	= $d?holderMakeArg($d):$widget['data'];

	$widget['exec']	= $exec;
	
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
function holderSetValue($name, $val, $data)
{
	if (!$name) return;
	
	$d	= &$data;
	foreach(explode('.', $name) as $n){
		if ($n) $d	= &$d[$n];
	}
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