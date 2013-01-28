<?
function doc_page(&$db, $val, &$data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;

	$menu = array();
	if (access('write', "page:$id"))
	{
		$menu['Изменить#popup'] = getURL("page_edit_$id");
		$menu[':layout'] = $data['title'];
	}
	$fn = getFn('doc_page_default');
	return $fn?$fn($db, &$menu, &$data):NULL;
}
?>
