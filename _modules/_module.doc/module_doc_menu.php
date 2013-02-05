<?
function doc_menu($id, &$data){
	$menu = array();

	if (access('add', "doc:$data[doc_type]:article"))
		$menu['Добавть документ#ajax_edit']	= getURL("page_add_$id", 'type=article');

	if (access('add', "doc:$data[doc_type]:page"))
		$menu['Добавть раздел#ajax_edit']	= getURL("page_add_$id", 'type=page');

	if (access('add', "doc:$data[doc_type]:product"))
		$menu['Добавть товар#ajax_edit']	= getURL("page_add_$id", 'type=product');

	if (access('add', "doc:$data[doc_type]:catalog"))
		$menu['Добавть каталог#ajax_edit']	= getURL("page_add_$id", 'type=catalog');

	if (access('write', "doc:$id"))
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");

	if (access('delete', "doc:$id"))
		$menu['Удалить#ajax']	= getURL("page_edit_$id", 'delete');

	return $menu;
}
?>