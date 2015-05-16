<?
function cookieSet($name, $val, $bStore = true)
{
	$time = $val && $bStore?time() + 3*7*24*3600:0;
	$_COOKIE[$name] = $val;
	setcookie($name, $val, $time, '/');
}
?>
