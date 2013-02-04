<?
function module_gallery($fn, &$data){
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("gallery_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
?>