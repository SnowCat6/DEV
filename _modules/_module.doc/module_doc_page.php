<?
function doc_page(&$db, $val, &$data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;

	$menu = array();
	if (access('write', "doc:$id"))
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");
	if (access('delete', "doc:$id"))
		$menu['Удалить#ajax_edit']	= getURL("page_edit_$id", 'delete');

	$fn = getFn('doc_page_default');
	return $fn?$fn($db, &$menu, &$data):NULL;
}
?>
