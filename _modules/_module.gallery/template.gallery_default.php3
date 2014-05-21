<?
function gallery_default_before($val, &$data){
	$fn	= getFn('gallery_plain');
	$fn2= getFn('gallery_plain_before');
	if ($fn2) $fn2($val, $data);
}
function gallery_default($val, &$data)
{
	$fn	= getFn('gallery_plain');
	if ($fn) $fn($val, $data);
}
?>