<?
function lockMessage(){
	$lock	= config::get('lockMessage', 0);
	config::set('lockMessage', $lock + 1);
}
function unlockMessage(){
	$lock	= config::get('lockMessage', 0);
	config::set('lockMessage', $lock - 1);
}
//	message, message:error, message:sql
function module_message($val, $data)
{
	$lock	= config::get('lockMessage', 0);
	if ($lock) return;
	
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

	$log	= config::get('log', array());
	list($name, $v)	= explode(':', $val, 2);
	$log[$name][]	= array($v, $data);
	config::set('log', $log);
}
function messageBox($message)
{
	if (!$message) return;
	m('fileLoad', 'css/core.css');
	echo "<div class=\"message\">$message</div>";
}
?>