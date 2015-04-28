<?
function lockMessage(){
	global $_CONFIG;
	$_CONFIG['lockMessage'] += 1;
}
function unlockMessage(){
	global $_CONFIG;
	$_CONFIG['lockMessage'] -= 1;
}
//	message, message:error, message:sql
function module_message($val, &$data)
{
	global $_CONFIG;
	if ($_CONFIG['lockMessage']) return;
	
	if ($val == '' || $val == 'error')
	{
		if (is_array($data)) $data = implode(' ', $data);
		$data = rtrim($data);
		if (!$data) return;
		m('fileLoad', 'css/core.css');
		$messageClass = $val?'message error':'message';
		return module('page:display:message', "<div class=\"$messageClass shadow\">$data</div>");
	}
	
	if (!$data) return;
/*
$f = fopen('fn.txt', 'a');
fwrite($f, "$data\r\n");
fclose($f);
*/
	global $_CONFIG;
	$log	= &$_CONFIG['log'];
	if (!$log) $log	= array();

	list($name, $v)	= explode(':', $val, 2);
	$log[$name][]	= array($v, $data);

	return;
}
function messageBox($message)
{
	if (!$message) return;
	m('fileLoad', 'css/core.css');
	echo "<div class=\"message\">$message</div>";
}
?>