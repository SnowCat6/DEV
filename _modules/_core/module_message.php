<?
//	message, message:error, message:sql
function module_message($val, &$data)
{
	if (defined('restoreProcess')) return;
	
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