<?
function docInline($id, $menu)
{
	if (!$menu) return;
	
	$db					= module('doc');
	$menu[':inline']	= array(
		'inlineAction'	=> getURL("page_edit_$id", 'inline'),
		'folder'		=> $db->folder($id),
		'dataPrefix'	=> 'doc'
	);
	return $menu;
}
/**********************************/
function doc_menu_inlineEx($menu, &$data, $fieldName)
{
	if (!$menu) return;
	
	$db					= module('doc', $data);
	$id					= $db->id();
	$inline				= array();
	$inline['action']	= getURL("page_edit_$id", 'inline');
	$inline['folder']	= $db->folder($id);
	$inline['dataName']	= docdbMakeFieldName($fieldName);
	$inline['data']		= dataByName($fieldName, $data);
	$menu[':inline']	= $inline;
	return $menu;
}
function doc_menu_inline($id, &$data, $fieldName, $bSimple = true)
{
	$menu	= doc_menu($id, $data, $bSimple);
	return doc_menu_inlineEx($menu, $data, $fieldName);
}
function docdbMakeFieldName($fieldName){
	return 'doc[' . str_replace('.', '][', $fieldName) . ']';
}
function dataByName($fieldName, &$data){
	$d = $data;
	$f = explode('.', $fieldName);
	foreach($f as $name) $d = &$d[$name];
	return '' . $d;
}
/************************************/
function doc_menu($id, &$data, $bSimple = true)
{
	if (!$data) return;
	
	$menuItems	= '';
	
	if (is_string($bSimple) && $bSimple[0] == '+')
	{
		$menuItems	= substr($bSimple, 1);
		$bSimple	= true;
	}else
	if (is_string($bSimple) && $bSimple[0] == '-')
	{
		$menuItems	= substr($bSimple, 1);
		$bSimple	= false;
	}
	
	if (is_string($bSimple)) $menuItems = $bSimple;
	else if ($bSimple == true) $menuItems = "drag,edit,$menuItems";
	else $menuItems = "drag,add,edit,delete,$menuItems";
	
	$menuItems	= explode(',', $menuItems);
	$menu		= array();
	
	foreach($menuItems as $menuItem)
	{
		$fn	= getFn("doc_menu_$menuItem");
		if ($fn) $fn($id, $data, $menu);
	}
	
	return $menu;
}

function doc_menu_drag($id, &$data, &$menu)
{
	if (!access('write', "doc:$id")) return;
	$menu[':draggable']			= docDraggableID($id, $data);
}
function doc_menu_edit($id, &$data, &$menu)
{
	if (!access('write', "doc:$id")) return;

	$menu['Изменить#ajax_edit']	= array(
		'href'	=> getURL("page_edit_$id"),
		'title'	=> 'Изменить документ'
	);
}
function doc_menu_delete($id, &$data, &$menu)
{
	if (!access('delete', "doc:$id")) return;

	$menu['Удалить']	= array(
		'href'	=> getURL("page_edit_$id", 'delete'),
		'title'	=> 'Удалить документ навсегда'
	);
	m('script:doc_delete');
}

function doc_menu_sortable($id, &$data, &$menu)
{
	if (!access('write', "doc:$id")) return;
	$menu[':sortable']	= "doc:$id";
}

function doc_menu_add($id, &$data, &$menu)
{
	if (access('add', "doc:$id:article")){
		$docType	= docTypeEx('article', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=article');
	}

	if (access('add', "doc:$id:page")){
		$docType	= docTypeEx('page', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=page');
	}

	if (access('add', "doc:$id:product")){
		$docType	= docTypeEx('product', $data['template']);
		$menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=product');
	}

	if (access('add', "doc:$id:catalog")){
		$docType	= docTypeEx('catalog', $data['template'], 0, false);
		if ($docType) $menu["+$docType#ajax_edit"]	= getURL("page_add_$id", 'type=catalog');
	}

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
