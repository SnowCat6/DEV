<?
//	doc:title:mask	=> path to mask
//	doc:title:		=> width or array(w,h)
//	+function doc_titleImage
function doc_titleImage(&$db, &$mode, &$data)
{
	list($id, $mode)= explode(':', $mode, 2);

	$renderName	= access("write", "doc:$id")?'':hashData($data);
	$cache		= module("doc:cacheGet:$id:titleImage_$renderName");
	if (!$cache)
	{
		$d		= $db->openID($id);
		if (!$d) return;
		$noCache= getNoCache();
		$folder	= $db->folder($id);

		if (!is_array($data)) $data = array();
		$data['property']['title']	= $d['title'];
		$data['uploadFolder']		= array("$folder/Title", "$folder/Gallery");
		$cache	= m("file:image:doc$id", $data);

		if ($noCache == getNoCache()){
			module("doc:cacheSet:$id:titleImage_$renderName", $cache);
		}
	}

	echo $cache;
}

//	Вывести заголовок документа с сылкой на документ
function doc_name($db, $id, $option)
{
	$data = $db->openID(alias2doc($id));
	if (!$data) return;

	$name = htmlspecialchars($data['title']);
	if ($option == 'link')
	{
		$class	= $data['fields']['class'];
		if ($class) $class = "class=\"$class\"";
		$url	= getURL($db->url($id));
		$name	= "<a href=\"$url\"$class>$name</a>";
	};
	echo $name;
}
function doc_price($db, $id, $data)
{
	$id		= alias2doc($id);
	$data	= $db->openID($id);
	if (!$data) return;
	echo docPrice($data);
}
function doc_path($db, $id, $data)
{
	if (!$id) $id = currentPage();

	$split	= '';
	$path	= getPageParents($id, true);

	foreach($path as $iid){
		echo $split;
		doc_name($db, $iid, "link");
		$split = $data['split']?$data['split']:' / ';
	}
}
function doc_class(&$db, $id, &$data){
	if (!$id) $id = currentPage();
	$data	= $db->openID($id);
	echo $data?$data['fields']['class']:'';
}
function docNote(&$data, $nLen = 200){
	return makeNote($data['document'], $nLen);
}
function currentPage($id = NULL){
	if ($id != NULL) $GLOBALS['_SETTINGS']['page']['currentPage'] = $id;
	else return @$GLOBALS['_SETTINGS']['page']['currentPage'];
}
function docDraggableID($id, &$data)
{
	if (!access('write', "doc:$id")) return;
	
	return dragID(array(
		'actionAdd'		=> "ajax_edit_$id.htm?ajax=itemAdd",
		'actionRemove'	=> "ajax_edit_$id.htm?ajax=itemRemove",
		'drag_type'		=> docDragAccess($data)
	));
}
function docDragAccess(&$data)
{
	$accept			= docDropAccess($data['doc_type'], $data['template']);
	$accept["doc_type:$data[doc_type]"]		= "doc_type:$data[doc_type]";
	$accept["doc_template:$data[template]"]	= "doc_template:$data[template]";
	$accept['doc']	= 'doc';
	return $accept;
}
function docDropAccess($type, $template)
{
	$accept	= array();
	if ($type)		$accept["type:$type"]			= "type:$type";
	if ($template)	$accept["template:$template"]	= "template:$template";
	if ($accept){
		$accept	= implode('_', $accept);
		$accept	= array("doc_$accept");
	}else $accept['doc'] = 'doc';
	
	return $accept;
}
function docStartDrop(&$search, $template)
{
	if ($search[':sortable'])
	{
		if (!isset($search[':sortable']['action']))		$search[':sortable']['action']		= 'ajax_edit.htm?ajax=itemSort';
		if (!isset($search[':sortable']['itemFilter']))	$search[':sortable']['itemFilter']	= '.admin_sort_handle';
		if (!isset($search[':sortable']['itemData']))	$search[':sortable']['itemData']	= 'sort_index';
	}
	startDrop($search, $template, false, docDropAccess($search['type'], $search['template']));
}
function docEndDrop()
{
	endDrop();
}
function docURL($id){
	$db = module('doc');
	return $db->url($id);
}
function currentPageRoot($index = 0)
{
	$thisID = currentPage();
	if (!$thisID) return;
	$parents= getPageParents($thisID);
	@$parent= $parents[$index];
	return @$parent?$parent:$thisID;
}
function getPageParents($id, $bUseThis = false)
{
	$parents	= $bUseThis?array($id):array();
	$prop		= module("prop:get:$id");
	while(@$parent= (int)$prop[':parent'])
	{
		if (is_int(array_search($parent, $parents))) break;
		$parents[] 	= $parent;
		$id			= $parent;
		$prop		= module("prop:get:$id");
	}
	return array_reverse($parents);
}
function docTitleImage($id){
	$db		= module('doc');
	$folder	= $db->folder($id);

	@list($name, $path) = each(getFiles("$folder/Title"));
	if ($path) return $path;

	@list($name, $path) = each(getFiles("$folder/Gallery"));
	return $path;
}
function alias2doc($val)
{
	if (is_array($val)) return makeIDS($val);
	if ($val == 'root')	return currentPageRoot();
	if ($val == 'this')	return currentPage();

	if (preg_match('#^(\d+)$#', $val))
		return (int)$val;
	if (preg_match('#/page(\d+)\.htm#', $val, $v))
		return (int)$v[1];

	$nativeURL	= module("links:getLinkBase", $val);
	if (!$nativeURL){
		$v			= "/$val.htm";
		$nativeURL	= module("links:getLinkBase", $v);
	}
	if ($nativeURL && preg_match('#/page(\d+)#', $nativeURL, $v))
		return (int)$v[1];
}
function docType($type, $n = 0)
{
	return docTypeEx($type, '', $n);
}
function docTypeEx($type, $template, $n = 0, $bUnkonName = true)
{
	$docTypes	= getCacheValue('docTypes');
	
	$names		= $docTypes["$type:$template"];
	if (!$names) $names = $docTypes["$type:"];
	if (!$names) return $bUnkonName?"Не известный тип, $type:$template":'';
	
	$names		= explode(':',  $names);
	$n			= min($n, count($names)-1);
	return $names[$n];
}
function showDocument($val, $data = NULL)
{
	//	{\{moduleName=values}\}
	//	Специальная версия для статических страниц
	$val= preg_replace_callback('#{{([^}]+)}}#u', 'parsePageModuleFn', $val);
	echo $val;
}
function parsePageModuleFn($matches)
{
	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$baseCode	= $matches[1];
	@list($moduleName, $moduleData) = explode('=', $baseCode, 2);
	//	name:val;nam2:val
	$module_data= array();
	$d			= explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if ($val){
			$d2		= &$module_data;
			$name	= explode('.', $name);
			foreach($name as $n) @$d2 = &$d2[$n];
			$d2	= $val;
		}else{
			$module_data[] = $name;
		}
	}
	
	return mEx($moduleName, $module_data);
}

//	+function doc_storage
function doc_storage($db, $mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];
	
	if (strncmp($id, 'doc', 3)) return;
	$docID	= (int)substr($id, 3);
	
	switch($mode){
	case 'set':
		$d	= array();
		$d['fields']['any'][':storage'][$name]	= $ev['content'];
		$bOK=  m("doc:update:$docID:edit", $d) != 0;
		m("doc:cacheClear:$docID");
		return $bOK;
	case 'get':
		$d		= $db->openID($docID);
		if (!$d) return;
		
		$ev['content']	= $d['fields']['any'][':storage'][$name];
		return true;
	}
}
?>