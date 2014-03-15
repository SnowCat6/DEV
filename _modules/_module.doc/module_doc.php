<?
function module_doc($fn, &$data)
{
	$sql		= array();
	$sql[]		= '`deleted` = 0';

	//	Если есть опция показывать скрытые, то она доступна только элите, для всех остальных игнорируется
	if (getValue('showHidden') && hasAccessRole('admin,developer,writer,manager') ){
	}else{
		$sql[]		= "`visible` = 1";
//		$sql[]		= "`visible` = 1 AND (`doc_type` <> 'product' OR `price` > 0)";
	}
	//	База данных пользователей
	$db 		= new dbRow('documents_tbl', 'doc_id');
	$db->sql	= implode(' AND ', $sql);
	$db->images = images.'/doc';
	$db->url 	= 'page';
	$db->setCache();
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("doc_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function doc_name($db, $id, $option){
	$data = $db->openID(alias2doc($id));
	if (!$data) return;

	$name = htmlspecialchars($data['title']);
	if ($option == 'link'){
		$url = getURL($db->url($id));
		echo "<a href=\"$url\">$name</a>";
	}else echo $name;
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
	$path	= getPageParents($id);
	foreach($path as $iid){
		echo $split;
		doc_name($db, $iid, "link");
		$split = ' / ';
	}
}
function docNote(&$data, $nLen = 200){
	return makeNote($data['originalDocument'], $nLen);
}
function currentPage($id = NULL){
	if ($id != NULL) $GLOBALS['_SETTINGS']['page']['currentPage'] = $id;
	else return @$GLOBALS['_SETTINGS']['page']['currentPage'];
}
function docDraggableID($id, &$data){
	if (!access('write', "doc:$id")) return;
	module('script:draggable');
	return " rel=\"draggable-doc-ajax_edit_$id-$data[doc_type]\"";
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
function getPageParents($id){
	$parents	= array();
	$prop		= module("prop:get:$id");
	while(@$parent= (int)$prop[':parent']['property']){
		if (is_int(array_search($parent, $parents))) break;
		$parents[] 	= $parent;
		$id			= $parent;
		$prop		= module("prop:get:$id");
	}
	return array_reverse($parents);
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
function docTypeEx($type, $template, $n = 0)
{
	$docTypes	= getCacheValue('docTypes');
	
	$names		= $docTypes["$type:$template"];
	if (!$names) $names = $docTypes["$type:"];
	if (!$names) return "Не известный тип, $type:$template";
	
	$names		= explode(':',  $names);
	$n			= min($n, count($names)-1);
	return $names[$n];
}
function docTitleImage($id){
	$db		= module('doc');
	$folder	= $db->folder($id);

	@list($name, $path) = each(getFiles("$folder/Title"));
	if ($path) return $path;

	@list($name, $path) = each(getFiles("$folder/Gallery"));
	return $path;
}
function doc_clear($db, $id, $data){
	$a = array();
	setCacheValue('textBlocks', $a);
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `document` = NULL");
	
	m('prop:clear');
}
function doc_recompile($db, $id, $data)
{
	$a = array();
	setCacheValue('textBlocks', $a);
	
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->setValue($ids, 'document', NULL, false);
	}else{
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
		
		$ddb	= module('doc');
		$db->open("`searchDocument` IS NULL");
		while($data = $db->next()){
			$d	= array();
			$d['searchTitle']	= docPrepareSearch($data['title']);
			$d['searchDocument']= docPrepareSearch($data['originalDocument']);
			$ddb->setValues($db->id(), $d);
			$db->clearCache();
		}
		
	}
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
	
	return m($moduleName, $module_data);
}

function doc_childs($db, $deep, &$search)
{
	$key	= $deep.':'.hashData($search);
	$cache	= memGet($key);
	if ($cache) return $cache;

	$tree	= array();
	$childs	= array();
	$deep	= (int)$deep;
	if ($deep < 1) return array();

	if (!$search['type']) $search['type'] = 'page,catalog';

	for($ix = 0; $ix < $deep; ++$ix)
	{
		$ids	= array();
		$db->open(doc2sql($search));
		while($db->next()){
			$id		= $db->id();
			$ids[]	= $id;
			$prop	= module("prop:get:$id");

			$parents= explode(', ', $prop[':parent']['property']);
			foreach($parents as $parent){
				$parent = (int)$parent;
				$childs[$parent][$id] = array();
				if ($ix == 0) $tree[$parent] = array();
			}
		}
		$search	= array('parent'=>$ids, 'type'=>$search['type']);
	}

	foreach($tree as $parent => &$c)
	{
		$c		= $childs[$parent];
		if (!is_array($c)) $c = '';
		
		$stop	= array();
		docMaketree($tree, $childs, $stop);
	}
	$tree[':childs'] = $childs;
	memSet($key, $tree);
	
	return $tree;
}

function docMakeTree(&$tree, &$childs, &$stop)
{
	foreach($tree as $parent => &$c)
	{
		if (isset($stop[$parent])) continue;
		$stop[$parent] = true;
		
		$c = $childs[$parent];
		if (!is_array($c)) $c = '';
		else docMakeTree($c, $childs, $stop);
	}
}
//	doc:title:mask	=> path to mask
//	doc:title:		=> width or array(w,h)
function doc_titleImage(&$db, &$mode, &$data)
{
	list($id, $mode) = explode(':', $mode, 2);
	if ($mode == 'mask')
	{
		$mask	= $data['mask'];
		if (!$mask) return;
		
		$bPopup	= $data['popup']=='false'?false:true;
		if ($bPopup) m('script:lightbox');
		$title	= module("doc:cacheGet:$id:titleImageMask:$mask");
		
		if (!isset($title))
		{
			if ($data['title'] != 'false'){
				$d	= $db->openID($id);
				$t	= $d['title'];
			}
			ob_start();
			$image = module("doc:titleImage:$id");
			$title = displayThumbImageMask($image, $mask, '', $t, $bPopup?$image:'');
			if (!$title && $data['noImage']) echo "<img src=\"$data[noImage]\" />";
			$title	= ob_get_clean();
			m("doc:cacheSet:$id:titleImageMask:$mask", $title);
		}
		echo $title;
		return;
	}else
	if ($mode == 'size'){
		$w = 0; $h = 0;
		if (is_array($data)){
			$w		= $data[0]; $h = $data[1];
			if (count($data) == 1){
				list($w, $h) = explode('x', $w);
			}
		}else{
			list($w, $h) = explode('x', $w);
		}
		if ($h){
			$name	= $w.'x'.$h;;
			$w		= array($w, $h);
		}else{
			$name	= $w;
		}

		$title	= module("doc:cacheGet:$id:titleImageSize:$name");
		if (!$title){
			$d	= $db->openID($id);
			$t	= $d['title'];
		
			ob_start();
			$t2	= module("doc:titleImage:$id");
			displayThumbImage($t2, $w, '', $t);
			$title	= ob_get_clean();
			m("doc:cacheSet:$id:titleImageSize:$name", $title);
		}
		echo $title;
		return;
	}

	$title	= module("doc:cacheGet:$id:titleImage");
	if (!isset($title)){
		$title = docTitleImage($id);
		m("doc:cacheSet:$id:titleImage", "$title");
	}

	if ($data){
		$w = 0; $h = 0;
		if (is_array($data)){
			$w		= $data[0]; $h = $data[1];
			$name	= $w.'x'.$h;;
		}else{
			$w 		= $data;
			$name	= $w;
		}
		
		$t	= module("doc:cacheGet:$id:titleImage:$name");
		if (isset($t)) return $t;
		
		ob_start();
		$title	= displayThumbImage($title, $data);
		ob_get_clean();
		m("doc:cacheSet:$id:titleImage:$name", $title);
	}
	return $title;
}
function doc_find(&$db, &$val, &$search){
	$db->open(doc2sql($search));
	return $db;
}
?>
