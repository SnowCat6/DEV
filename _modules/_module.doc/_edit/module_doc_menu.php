<?
function doc_menu($id, &$data, $bSimple = true)
{
	$menu		= array();
	$bHiddable	= false;

	if (!$bSimple && access('add', "doc:$data[doc_type]:article"))
		$menu['Добавть документ#ajax_edit']	= getURL("page_add_$id", 'type=article');

	if (!$bSimple && access('add', "doc:$data[doc_type]:page"))
		$menu['Добавть раздел#ajax_edit']	= getURL("page_add_$id", 'type=page');

	if (!$bSimple && access('add', "doc:$data[doc_type]:product")){
		$menu['Добавть товар#ajax_edit']	= getURL("page_add_$id", 'type=product');
	}

	if (!$bSimple && access('add', "doc:$data[doc_type]:catalog"))
		$menu['Добавть каталог#ajax_edit']	= getURL("page_add_$id", 'type=catalog');

	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");
		$menu[':draggable']			= docDraggableID($id, $data);
	}

	if (!$bSimple && access('delete', "doc:$id"))
		$menu['Удалить#ajax_dialog']	= getURL("page_edit_$id", 'delete');
		
	return $menu;
}

function doc_admin($db, $val, $data)
{
	@list($action, $id, $type) = explode(':', $val);
	$id		= alias2doc($id);

	switch($action){
	case 'add':
		$data	= $db->openID($id);
		if (!access('add', "doc:$data[doc_type]:$type")) return;
		$url	= getURL("page_add_$id", "type=$type");
		echo " <a href=\"$url\" id=\"ajax_edit\">+</a>";
		break;
	}
}
?>