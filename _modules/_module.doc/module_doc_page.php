<?
function doc_page(&$db, $val, &$data)
{
	@list($id, $template) = explode(':', $val);
	$id		= $data?(int)$data[1]:(int)$id;
	$data	= $db->openID($id);
	if (!$data) return;
		
	$fn = getFn("doc_page_$template");
	if (!$fn) $fn = getFn('doc_page_default');
	return $fn?$fn($db, doc_menu($id, $data), &$data):NULL;
}
?>
