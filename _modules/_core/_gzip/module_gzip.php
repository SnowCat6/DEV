<?
function module_gzip($val, &$Contents)
{
	$ini = getIniValue(':');
	if ($ini['compress'] != 'gzip') return;

    if (headers_sent() || connection_aborted())
        return; 
		
    $HTTP_ACCEPT_ENCODING = $_SERVER['HTTP_ACCEPT_ENCODING']; 
    if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false)	$HTTP_ACCEPT_ENCODING = "x-gzip"; 
	else
    if (strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== false) 	$HTTP_ACCEPT_ENCODING = "gzip"; 
	else 
		return;

	header("Content-Encoding: $HTTP_ACCEPT_ENCODING"); 
	print "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
	$Size	= strlen($Contents); 
	$Crc	= crc32($Contents); 
	$Contents	= gzcompress($Contents, 3); 
	$Contents	= substr($Contents,  0,  strlen($Contents) - 4); 
	$Contents	.=pack('V', $Crc).pack('V', $Size);
}
?>