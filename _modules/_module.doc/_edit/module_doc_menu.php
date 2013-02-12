<?
function doc_menu($id, &$data, $bSimple = false){
	$menu = array();

	if (!$bSimple && access('add', "doc:$data[doc_type]:article"))
		$menu['Добавть документ#ajax_edit']	= getURL("page_add_$id", 'type=article');

	if (!$bSimple && access('add', "doc:$data[doc_type]:page"))
		$menu['Добавть раздел#ajax_edit']	= getURL("page_add_$id", 'type=page');

	if (!$bSimple && access('add', "doc:$data[doc_type]:product"))
		$menu['Добавть товар#ajax_edit']	= getURL("page_add_$id", 'type=product');

	if (!$bSimple && access('add', "doc:$data[doc_type]:catalog"))
		$menu['Добавть каталог#ajax_edit']	= getURL("page_add_$id", 'type=catalog');

	if (access('write', "doc:$id"))
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");

	if (!$bSimple && access('delete', "doc:$id"))
		$menu['Удалить#ajax']	= getURL("page_edit_$id", 'delete');
		
	if ($menu){
		$menu[':draggable'] = "doc-page_edit_$id-$data[doc_type]";
	}

	return $menu;
}
?>