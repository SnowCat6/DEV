<?
//	+function text_uppercase
function text_uppercase($val, &$data)
{
//	$data	= utf8_decode($data);
	$data	= strtoupper($data);
//	$data	= utf8_encode($data);
	return $data;
}
//	+function text_lowercase
function text_lowercase($val, &$data)
{
//	$data	= utf8_decode($data);
	$data	= strtolower($data);
//	$data	= utf8_encode($data);
	return $data;
}
?>
