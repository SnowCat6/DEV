<?
$GLOBALS['_SETTINGS']['log']	= array();
//	message, message:error, message:sql
function module_message($val, &$data)
{
//	if (!hasAccessRole('developer')) return;

	if ($val == '' || $val == 'error')
	{
		if (is_array($data)) $data = implode(' ', $data);
		$data = rtrim($data);
		if (!$data) return;
		$messageClass = $val?'message error':'message';
		return module('page:display:message', "<div class=\"$messageClass shadow\">$data</div>");
	}
	
	if (!$data) return;
	list($name, $v)	= explode(':', $val, 2);
	$GLOBALS['_SETTINGS']['log'][$name][]	= array($v, $data);
	return;
}
function messageBox($message){
	if (!$message) return;
	echo "<div class=\"message\">$message</div>";
}
?>