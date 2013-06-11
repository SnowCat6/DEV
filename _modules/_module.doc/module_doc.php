<?
function module_doc($fn, &$data)
{
	$sql		= array();
	$sql[]		= '`deleted` = 0';

	//	Если есть опция показывать скрытые, то она доступна только элите, для всех остальных игнорируется
	if (getValue('showHidden') && hasAccessRole('admin.developer,writer,manager') ){
	}else{
		$sql[]		= '`visible` = 1';
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

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("doc_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function doc_name($db, $id, $data){
	$data = $db->openID(alias2doc($id));
	if (!$data) return;

	echo htmlspecialchars($data['title']);
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
	return "rel=\"draggable-doc-ajax_edit_$id-$data[doc_type]\"";
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
	if (preg_match('#^(\d+)$#', $val))
		return (int)$val;

	$v			= "/$val.htm";
	$nativeURL	= module("links:getLinkBase", $v);
	if ($nativeURL && preg_match('#/page(\d+)#', $nativeURL, $v))
		return (int)$v[1];
}
function docType($type, $n = 0)
{
	$docTypes	= getCacheValue('docTypes');
	$names		= explode(':',  $docTypes[$type]);
	return @$names[$n];
}
function docTitleImage($id){
	$db		= module('doc');
	$folder	= $db->folder($id);
	@list($name, $path) = each(getFiles("$folder/Title"));
	return $path;
}

function doc_recompile($db, $id, $data)
{
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->open("`doc_type` = 'product' AND `doc_id` IN ($ids)");
		while($data = $db->next()){
			compilePrice(&$data);
			$db->clearCache();
		}
		
		$ids = explode(',', $ids);
		foreach($ids as $id){
			if ($id) clearThumb($db->folder($id));
		}
		
		$db->setValue($ids, 'document', NULL, false);
	}else{
		$db->open("`doc_type` = 'product'");
		while($data = $db->next()){
			compilePrice(&$data);
			$db->clearCache();
		}
		$ddb	= module('doc');
		$db->open("`searchDocument` IS NULL");
		while($data = $db->next()){
			$d	= array();
			$d['searchTitle']	= docPrepareSearch($data['title']);
			$d['searchDocument']= docPrepareSearch($data['originalDocument']);
			$ddb->setValues($db->id(), $d);
			$db->clearCache();
		}
		
		$a = array();
		setCacheValue('textBlocks', $a);
		clearThumb(images);
		
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
	}
}
function showDocument($val, $data = NULL)
{
	//	{\{moduleName=values}\}
	//	Специальная версия для статических страниц
	$val= preg_replace_callback('#{{([^}]+)}}#u', parsePageModuleFn, $val);
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
			$d		= &$module_data;
			$name	= explode('.', $name);
			foreach($name as $n){
				@$d = &$data[$n];
			}
			$d	= $val;
		}else{
			$module_data[] = $name;
		}
	}
	
	return m($moduleName, $module_data);
}


?>