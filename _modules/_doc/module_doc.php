<?
function module_doc($fn, &$data)
{
	$sql		= array();

	//	Если есть опция показывать скрытые, то она доступна только элите, для всех остальных игнорируется
	if (getValue('showHidden') && hasAccessRole('admin,developer,writer,manager') ){
	}else{
		$sql[]		= "`visible` = 1";
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
		$class	= $data['fields']['class'];
		if ($class) $class = "class=\"$class\"";
		$url	= getURL($db->url($id));
		echo "<a href=\"$url\"$class>$name</a>";
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
	if (!$path) return;

	echo '<div class="docPath">';
	foreach($path as $iid){
		echo $split;
		doc_name($db, $iid, "link");
		$split = htmlspecialchars($data['split']?$data['split']:' / ');
	}
	echo '</div>';
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
		'drag_type'		=> array('doc', "doc_$data[doc_type]")
	));
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
	while(@$parent= (int)$prop[':parent']){
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
function docTitleImage($id){
	$db		= module('doc');
	$folder	= $db->folder($id);

	@list($name, $path) = each(getFiles("$folder/Title"));
	if ($path) return $path;

	@list($name, $path) = each(getFiles("$folder/Gallery"));
	return $path;
}
function doc_clear($db, $id, $data)
{
	$table	= $db->table();
	$db->exec("UPDATE $table SET `cache` = NULL");
	m('prop:clear');
	m('cache:clear');
	clearCache();
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

function doc_childs($db, $deep, &$search)
{
	$key	= $deep.':'.hashData($search);
	$cache	= memGet($key);
	if ($cache) return $cache;

	$tree	= array();
	$childs	= array();
	$d		= array();
	$deep	= (int)$deep;
	if ($deep < 1) return array();

	if (!$search['type']) $search['type'] = 'page,catalog';

	for($ix = 0; $ix < $deep; ++$ix)
	{
		$ids	= array();
		$db->open(doc2sql($search));
		while($data	= $db->next())
		{
			unset($data['cache']);
			$id		= $db->id();
			$ids[]	= $id;
			$d[$id]	= $data;
			$prop	= module("prop:get:$id");

			$parents= explode(', ', $prop[':parent']);
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
	$tree[':childs']= $childs;
	$tree[':data']	= $d;
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
function doc_find(&$db, &$val, &$search){
	$db->open(doc2sql($search));
	return $db;
}
?>
