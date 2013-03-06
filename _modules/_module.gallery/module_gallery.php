<?
function module_gallery($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	if (!$fn) $fn = 'default';
	$fn = getFn("gallery_$fn");
	if (!$fn) return;
	
	if (!@$data['src']){
		$id		= currentPage();
		if ($id){
			module('script:lightbox');
			$db	= module('doc');
			$d	= $db->openID($id);
			if (beginCompile($d, "gallery/$val"))
			{
				$data['src']= $db->folder($id).'/Gallery';
				$fn($val, $data);
				endCompile($d, "gallery/$val");
			}
			return;
		}
	}
	
	return $fn($val, $data);
}
?>
