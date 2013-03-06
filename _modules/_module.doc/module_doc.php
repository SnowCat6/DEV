<?
function module_doc($fn, &$data)
{
	//	База данных пользователей
	$db 		= new dbRow('documents_tbl', 'doc_id');
	$db->sql	= 'deleted = 0';
	$db->images = images.'/doc';
	$db->url 	= 'page';
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
function docDraggableID($id, &$data){
	if (!access('write', "doc:$id")) return;
	module('script:draggable');
	return "rel=\"draggable-doc-page_edit_$id-$data[doc_type]\"";
}
function currentPage($id = NULL){
	if ($id != NULL) $GLOBALS['_SETTINGS']['page']['currentPage'] = $id;
	else return @$GLOBALS['_SETTINGS']['page']['currentPage'];
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
function docPrice(&$data, $name = ''){
	if ($name == '') $name = 'base';
	@$price	= $data['fields'];
	@$price	= $price['price'];
	@$price = (float)$price[$name];
	return $price;
}
function priceNumber($price){
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = ''){
	$price = docPrice(&$data, $name);
	if (!$price) return;
	
	$price = priceNumber($price);
	return "<span class=\"price\">$price</span>";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat(&$data, $name);
	if ($price) $price = "<span class=\"priceName\">Цена: $price руб.</span>";
	return $price;
}
function docType($type, $n = 0)
{
	$docTypes	= getCacheValue('docTypes');
	$names		= explode(':',  $docTypes[$type]);
	return @$names[$n];
}
function docTitle($id){
	$db		= module('doc');
	$folder	= $db->folder($id);
	@list($name, $path) = each(getFiles("$folder/Title"));
	return $path;
}
function compilePrice(&$data, $bUpdate = true)
{
	$db	= module('doc', $data);
	$id	= $db->id();
	
	if ($price = docPrice($data))
	{
		$docPrice	= getCacheValue('docPrice');
		foreach($docPrice as $maxPrice => $name){
			if ($price >= $maxPrice) continue;
			$data[':property']['Цена'] = $name;
			break;
		}
		if ($price >= $maxPrice){
			$data[':property']['Цена'] = "> $maxPrice";
		}
	}else{
			$data[':property']['Цена'] = '';
	}
	if ($bUpdate)
		module("prop:set:$id", $data[':property']);
}
function document(&$data){
	if (!beginCompile(&$data, 'document')) return;
	echo $data['originalDocument'];
	endCompile(&$data, 'document');
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	$rendered = &$data['document'];
	if (!is_array($rendered)){
		@$rendered = unserialize($rendered);
		if (!is_array($rendered)) $rendered = array();
	}

	@$compiled = $rendered[$renderName];
	if (isset($compiled) && localCacheExists()){
		echo $compiled;
		return false;
	}

	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName)
{
	$document	= ob_get_clean();
	event('document.compile', &$document);
	echo $document;
	if (!localCacheExists()) return;

	$db			= module('doc:', $data);
	$id			= $db->id();
	if (!$id){
		module('message:trace:error', "Document not compiled, $renderName");
		return;
	}
	module('message:trace', "Document compiled, $id => $renderName");
	$data['document'][$renderName] = $document;
	
	//	Сохранить данные
	$db->setValue($id, 'document', $data['document'], false);
}
function doc_recompile($db, $id, $data){
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->open("`doc_type` = 'product' AND `doc_id` IN ($ids)");
		while($data = $db->next()){
			compilePrice(&$data);
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
		}
		$ddb	= module('doc');
		$db->open("`searchDocument` IS NULL");
		while($data = $db->next()){
			$d	= array();
			$d['searchTitle']	= docPrepareSearch($data['title']);
			$d['searchDocument']= docPrepareSearch($data['originalDocument']);
			$ddb->setValues($db->id(), $d);
		}
		
		$a = array();
		setCacheValue('textBlocks', $a);
		clearThumb(images);
		
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
	}
}
?>