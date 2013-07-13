<?
//	message, message:error, message:sql
function module_message($val, &$data)
{
	if ($val == '' || $val == 'error')
	{
		if (is_array($data)) $data = implode(' ', $data);
		$data = rtrim($data);
		if (!$data) return;
		$messageClass = $val?'message error':'message';
		return module('page:display:message', "<div class=\"$messageClass shadow\">$data</div>");
	}
	
	if (is_array($data)){
		ob_start();
		print_r($data);
		$data = ob_get_clean();
	}
	
	$data = rtrim($data);
	if (!$data) return;
	
	$hasError	= strpos($val, 'error');
	$class		= $hasError?' class="errorMessage"':'';
	@list($val, $type)	= explode(':', $val);
	if (!$type)$type= $val;
	
	switch($val){
	case 'sql':
		$val	= 'logSQL';
	break;
	case 'trace':
		$val	= 'logTrace';
	break;
	default:
		$val	= 'log';
	}
	module("page:display:$val", "<span$class>$type: <span>$data</span></span>\r\n");
	if ($hasError) module("page:display:log", "<span$class>$type: <span>$data</span></span>\r\n");
}
function messageBox($message){
	if (!$message) return;
	echo "<div class=\"message\">$message</div>";
}
?>