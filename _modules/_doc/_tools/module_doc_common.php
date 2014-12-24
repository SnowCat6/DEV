<?
//	doc:title:mask	=> path to mask
//	doc:title:		=> width or array(w,h)
//	+function doc_titleImage
function doc_titleImage(&$db, &$mode, &$data)
{
	if (!is_array($data)) $data = array();
	list($id, $mode)= explode(':', $mode, 2);
	
	if (access("write", "doc:$id"))
	{
		$d		= $db->openID($id);
		if (!$d) return;
		
		$folder	= $db->folder($id);
		if (!isset($data['property']['title'])) $data['property']['title']	= $d['title'];
		$data['uploadFolder']		= array("$folder/Title", "$folder/Gallery");
		return moduleEx("file:image:doc$id", $data);
	}

	$hash	= hashData($data);
	if (!beginCache("titleImage$hash", "doc$id")) return;

	$d		= $db->openID($id);
	if ($d)
	{
		$folder	= $db->folder($id);
		if (!isset($data['property']['title'])) $data['property']['title']	= $d['title'];
		$data['uploadFolder']		= array("$folder/Title", "$folder/Gallery");
		moduleEx("file:image:doc$id", $data);
	}
	
	endCache();
}
function doc_title($db, $id, $data)
{
	$data	= doc_data($db, $id, $data);
	echo htmlspecialchars($data['title']);
}
function doc_url($db, $id, $data)
{
	echo getURL($db->url(alias2doc($id)));
}
function doc_data(&$db, $id, $data){
	return $db->openID(alias2doc($id));
}
function doc_link(&$db, $id, &$property)
{
	if (!is_array($property)) $property = array();

	$id		= alias2doc($id);
	$data	= $db->openID($id);
	if (!$data) return;
	
	$property['href']	= getURL($db->url($id));
	$property['class']	= $data['fields']['class'];
	$property['title']	= $data['title'];

	$property	= makeProperty($property);
	$title		= htmlspecialchars($data['title']);
	echo "<a $property>$title</a>";
}
function doc_price($db, $id, $data)
{
	$data	= $db->openID(alias2doc($id));
	if (!$data) return;
	echo docPrice($data);
}
function doc_path($db, $id, $data)
{
	if (!$id) $id = currentPage();

	$split		= '';
	$property	= array();
	$path		= getPageParents($id, true);

	foreach($path as $iid)
	{
		echo $split;
		doc_link($db, $iid, $property);
		$split	= $data['split']?$data['split']:' / ';
	}
}
function doc_class(&$db, $id, &$data)
{
	if (!$id) $id = currentPage();
	$data	= $db->openID($id);
	echo $data?$data['fields']['class']:'';
}
function docNote(&$data, $nLen = 200){
	return makeNote($data['document'], $nLen);
}
function currentPage($id = NULL)
{
	global $_CONFIG;
	if ($id != NULL) $_CONFIG['page']['currentPage'] = $id;
	else return @$_CONFIG['page']['currentPage'];
}
function docDraggableID($id, &$data)
{
	if (!access('write', "doc:$id")) return;
	
	return module('dragID', array(
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
	module('startDrop', array(
		'search'	=> $search,
		'template'	=> $template,
		'accept'	=> docDropAccess($search['type'], $search['template'])
	));
}
function docEndDrop()
{
	module('endDrop');
}
function docURL($id){
	$db = module('doc');
	return $db->url($id);
}
function currentPageRoot($index = 0)
{
	return getPageRoot(currentPage(), $index);
}
function getPageRoot($thisID, $index = 0)
{
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
	
	$data	= getCache("alias2doc:$val", 'ram');
	if (!is_null($data)) return $data;

	if (preg_match('#^(\d+)$#', $val)){
		$data	= (int)$val;
	}else
	if (preg_match('#/page(\d+)\.htm#', $val, $v)){
		$data	= (int)$v[1];
	}else{
		$nativeURL	= module("links:getLinkBase", $val);
		if (!$nativeURL){
			$v			= "/$val.htm";
			$nativeURL	= module("links:getLinkBase", $v);
		}
		if ($nativeURL && preg_match('#/page(\d+)#', $nativeURL, $v)){
			$data	= (int)$v[1];
		}else{
			$data = '';
		}
	}

	setCache("alias2doc:$val", $data, 'ram');
	return $data;
}
function alias2docRaw($val)
{
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

?>