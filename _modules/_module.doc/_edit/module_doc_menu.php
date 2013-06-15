<?
function doc_menu($id, &$data, $bSimple = true)
{
	$menu		= array();
	$bHiddable	= false;

	if (!$bSimple && access('add', "doc:$id:article")){
		$docType	= docType('article');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=article');
	}

	if (!$bSimple && access('add', "doc:$id:page")){
		$docType	= docType('page');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=page');
	}

	if (!$bSimple && access('add', "doc:$id:product")){
		$docType	= docType('product');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=product');
	}

	if (!$bSimple && access('add', "doc:$id:catalog")){
		$docType	= docType('catalog');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=catalog');
	}

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