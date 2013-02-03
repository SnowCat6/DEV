<?
function doc_menu($id, &$data){
	$menu = array();

	if (access('write', "doc:add:$data[doc_type]:article"))
		$menu['Добавть документ#ajax_edit']	= getURL("page_add_$id", 'type=article');

	if (access('write', "doc:$id"))
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");

	if (access('delete', "doc:$id"))
		$menu['Удалить#ajax']	= getURL("page_edit_$id", 'delete');

	return $menu;
}
?>