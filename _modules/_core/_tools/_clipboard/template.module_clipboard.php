<? function module_clipboard($fn, &$data)
{
	list($fn, $val) = explode(':', $fn, 2);
	$fn	= getFn("clipboard_$fn");
	if ($fn) return $fn($val, $data);
}
function clipboard_get($val, $data){
	if (!hasAccessRole('admin,developer,editor,writer')) return;
	
	$res	= array();
	foreach($_COOKIE as $name => $val)
	{
		if (strncmp($name, 'clipboard:', strlen('clipboard:'))) continue;
		$name		= substr($name, strlen('clipboard:'));
		$res[$name]	= unserialize($val);
	}
	return $res;
}
function clipboard_add($group, $data)
{
	if (!hasAccessRole('admin,developer,editor,writer')) return;

	$name	= "clipboard:$group";
	$cookie	= unserialize($_COOKIE[$name]);
	if (!is_array($cookie)) $cookie = array();
	$cookie	= array_values($cookie);
	
	$cookie	= array_flip($cookie);
	$cookie[$data] = ''; unset($cookie[$data]);
	$cookie	= array_flip($cookie);

	$cookie[]	= $data;
	if (count($cookie) > 10){
		array_splice($cookie, 0, count($cookie) - 10);
	}

	cookieSet($name, serialize($cookie));
}
?>