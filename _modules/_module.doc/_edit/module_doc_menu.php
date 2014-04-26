<?
function doc_menu_inlineEx($menu, &$data, $fieldName)
{
	if (!$menu) return;
	
	$db					= module('doc', $data);
	$id					= $db->id();
	$inline				= array();
	$inline['action']	= getURL("page_edit_$id", 'inline');
	$inline['folder']	= $db->folder($id);
	$inline['dataName']	= 'doc['.$fieldName.']';
	$inline['data']		= $data[$fieldName];
	$menu[':inline']	= $inline;
	return $menu;
}
function doc_menu_inline($id, &$data, $fieldName, $bSimple = true)
{
	$menu	= doc_menu($id, $data, $bSimple);
	return doc_menu_inlineEx($menu, $data, $fieldName);
}
function doc_menu($id, &$data, $bSimple = true)
{
	if (!$data) return;
	
	$menu		= array();
	$bHiddable	= false;

	if (!$bSimple && access('add', "doc:$id:article")){
		$docType	= docTypeEx('article', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=article');
	}

	if (!$bSimple && access('add', "doc:$id:page")){
		$docType	= docTypeEx('page', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=page');
	}

	if (!$bSimple && access('add', "doc:$id:product")){
		$docType	= docTypeEx('product', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=product');
	}

	if (!$bSimple && access('add', "doc:$id:catalog")){
		$docType	= docTypeEx('catalog', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=catalog');
	}

	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");
		$menu[':draggable']			= docDraggableID($id, $data);
	}

	if (!$bSimple && access('delete', "doc:$id")){
		$menu['Удалить']	= getURL("page_edit_$id", 'delete');
		m('script:doc_delete');
	}
		
	return $menu;
}

function doc_admin($db, $val, $data)
{
	@list($action, $id, $type) = explode(':', $val);
	$id		= alias2doc($id);

	switch($action){
	case 'add':
		$data	= $db->openID($id);
		if (!access('add', "doc:$id:$type")) return;
		$url	= getURL("page_add_$id", "type=$type");
		echo " <a href=\"$url\" id=\"ajax_edit\" class=\"adminLink\" title=\"Добавить\">+</a>";
		break;
	}
}
?>
