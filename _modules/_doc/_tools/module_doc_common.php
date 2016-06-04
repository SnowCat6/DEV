<?
//	doc:title:mask	=> path to mask
//	doc:title:		=> width or array(w,h)
//	+function doc_titleImage
function doc_titleImage(&$db, &$mode, &$data)
{
	if (!is_array($data)) $data = array();
	list($id, $mode)= explode(':', $mode, 2);
	$id	= alias2doc($id);
	
	if (access("edit", "doc:$id"))
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

	$split		= $data['split']?$data['split']:' / ';
	$property	= array();
	$path		= getPageParents($id, true);
	$showed		= array();
	
	if ($data['showIndex']=="true" || !isset($data['showIndex'])){
		$url		= getURL();
		$showed[]	= "<a href=\"$url\">Главная</a>";
	}

	foreach($path as $iid)
	{
		ob_start();
		doc_link($db, $iid, $property);
		$showed[]	= ob_get_clean();
	}
	echo implode($split, $showed);
}
function doc_class(&$db, $id, &$data)
{
	if (!$id) $id = currentPage();
	$data	= $db->openID($id);
	echo $data?$data['fields']['class']:'';
}
function text_docNote($nLen, &$data){
	return $data = docNote($data, $nLen);
}
function docNote($data, $nLen = ''){
	if (!$nLen) $nLen = 200;
	return makeNote($data['document'], $nLen);
}
function docOrder($search)
{
	$order		= array();
	$docSort	= getCacheValue('docSort');
	$o			= $search[':order'];
	if (!$o) $o	= $search[':sort'];
	if (!$o) $search[':order'] = $o = 'default';
	
	foreach(explode(',', $o) as $orderName){
		$n		= trim($docSort[$orderName]);
		if ($n) $order[]	= $n;
	}
	return implode(',', $order);
}
function currentPage($id = NULL)
{
	if (is_null($id)) return config::get('currentPage');
	config::set('currentPage', $id);
}
function docDraggableID($id, $data, $query_data = NULL)
{
	if (!access('edit', "doc:$id")) return;
	
	if (is_array($query_data)){
		$q	= '&' . makeQueryString($query_data);
	}else $q = '';
	
	return module('dragID', array(
		'actionAdd'		=> "ajax_edit_$id.htm?ajax=itemAdd$q",
		'actionRemove'	=> "ajax_edit_$id.htm?ajax=itemRemove$q",
		'drag_type'		=> docDragAccess($data)
	));
}
function docDragAccess(&$data)
{
	$accept			= docDropAccess($data['doc_type'], $data['template']);
	$accept['doc']	= 'doc';
	return $accept;
}
function docDropAccess($type, $template)
{
	$accept	= array();
	if (!is_array($type))		$type		= $type?explode(',', $type):'';
	if (!is_array($template))	$template	= $template?explode(',', $template):'';
	
	foreach($type as $name)
	{
		if (!$name) continue;
		$accept["doc_type:$name"]		= "doc_type:$name";
		foreach($template as $name2){
			if ($name2) $accept["doc_type:$name"."_$name2"]		= "doc_type:$name"."_$name2";
		}
	}
	foreach($template as $name){
		if ($name)	$accept["doc_template:$name"]	= "doc_template:$name";
	}

	if (!$accept) $accept['doc'] = 'doc';
	
	return array_values($accept);
}
/**********************************/
function docStartDrop($search, $template)
{
	if ($search[':sortable'])
	{
		if (!isset($search[':sortable']['action']))		$search[':sortable']['action']		= 'ajax_edit.htm?ajax=itemSort';
		if (!isset($search[':sortable']['itemFilter']))	$search[':sortable']['itemFilter']	= '.admin_sort_handle';
		if (!isset($search[':sortable']['itemData']))	$search[':sortable']['itemData']	= 'sort_index';
	}
	$search[':accept']		= docDropAccess($search['type'], $search['template']);
	$search[':template']	= $template;
	module('startDrop', $search);
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
	$parents[]	= $thisID;
	$index	= min($index, count($parents)-1);
	return $index >=0 ? $parents[$index]:$thisID;
}
function getPageParents($id, $bUseThis = false)
{
	$key		= "docParents:$id";
	$parents	= getCache($key, 'ram');
	if (!is_array($parents)) 
	{
		$parents	= array();
		$prop		= module("prop:get:$id");
		while($parent= (int)$prop[':parent'])
		{
			if (is_int(array_search($parent, $parents))) break;
			$parents[] 	= $parent;
			$id			= $parent;
			$prop		= module("prop:get:$id");
		}
		setCache($key, $parents, 'ram');
	}
	
	$parents = array_reverse($parents);
	if ($bUseThis) $parents[] = $id;
	return $parents;
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
	list($type, $template)	= explode(':', $type, 2);
	return docTypeEx($type, $template, $n);
}
function docTypeEx($type, $template, $n = 0, $bUnkonName = true)
{
	$rules	= docConfig::getTemplates();
	$data	= $rules["$type:$template"];
	if (!$data) $data = $rules["$type:"];
	if (!$data) return $bUnkonName?"Не известный тип, $type:$template":'';
	return $data[$n==0?'NameOne':'NameOther'];
}

?>