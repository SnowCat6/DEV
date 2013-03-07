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
		return module('page:display:message', "<p class=\"$messageClass shadow\">$data</p>");
	}
	
	if (is_array($data)){
		ob_start();
		print_r($data);
		$data = ob_get_clean();
	}
	
	$data = rtrim($data);
	if (!$data) return;
	
	$class		= strpos($val, 'error')?' class="errorMessage"':'';
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
}
?>