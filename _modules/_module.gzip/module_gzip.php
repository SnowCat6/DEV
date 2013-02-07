<?
function module_gzip($val, &$Contents)
{
    if ($ENCODING = CheckCanGzip()){
        header("Content-Encoding: $ENCODING"); 
        print "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
        $Size	= strlen($Contents); 
        $Crc	= crc32($Contents); 
        $Contents	= gzcompress($Contents, 3); 
        $Contents	= substr($Contents,  0,  strlen($Contents) - 4); 
		$Contents	.=pack('V', $Crc).pack('V', $Size);
    }
}

function CheckCanGzip()
{
	$ini = getCacheValue('ini');
	if (@$ini[':']['compress'] != 'gzip') return;

    @$HTTP_ACCEPT_ENCODING = $_SERVER['HTTP_ACCEPT_ENCODING']; 
    if (headers_sent() || connection_aborted()){
        return; 
    }
    if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false)	return "x-gzip"; 
    if (strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== false) 	return "gzip"; 
    return; 
}
?>