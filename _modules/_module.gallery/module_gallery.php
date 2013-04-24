<?
function module_gallery($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	if (!$fn) $fn = 'default';
	$fn = getFn("gallery_$fn");
	if (!$fn) return;

	if (!is_array($data) || !$data)
	{
		$id	= (int)$data;

		if (!$id) $id = currentPage();
		if ($id && !defined("galleryShowed$id"))
		{
			define("galleryShowed$id", true);
			module('script:lightbox');
			
			$db	= module('doc');
			$d	= $db->openID($id);
			if (beginCompile($d, "gallery/$val"))
			{
				$data		= array();
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
