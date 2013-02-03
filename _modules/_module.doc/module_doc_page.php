<?
function doc_page(&$db, $val, &$data)
{
	$id		= $data?(int)$data[1]:(int)$val;
	$data	= $db->openID($id);
	if (!$data) return;
		
	$fn = getFn('doc_page_default');
	return $fn?$fn($db, doc_menu($id, $data), &$data):NULL;
}
?>
