<? function doc_cacheSet($db, $id, $cacheData)
{
	list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;

	$d						= array();
	$d['document']			= $data['document'];
	$d['document'][$name]	= $cacheData;
	$GLOBALS['_CONFIG']['docCache'][$id] = $d;
	
	$data['document'] = $d['document'];
	$db->setCacheData($id, $data);
	module('message:trace', "Document cache set, $id => $name");
}
function doc_cacheFlush($db, $val, $data)
{
	$cache		= &$GLOBALS['_CONFIG']['docCache'];
	if (!$cache) return;
	
	foreach($cache as $id => &$d){
		$d['id']	= $id;
		$iid		= $db->update($d);
	}
}
function doc_cacheGet($db, $id, $data)
{
	list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;
	
	return $data['document'][$name];
}
function getDocument(&$data){
	ob_start();
	document($data);
	return ob_get_clean();
}
function document(&$data){
	if (!beginCompile($data, 'document')) return;
	echo $data['originalDocument'];
	endCompile($data, 'document');
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	$id		= $data['doc_id'];
	$cache	= module("doc:cacheGet:$id:$renderName");
	if (isset($cache)){
		showDocument($cache, $data);
		return false;
	}
	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName)
{
	$id			= $data['doc_id'];
	$document	= ob_get_clean();
	event('document.compile', $document);
	showDocument($document, $data);
	if (!localCacheExists()) return;
	module("doc:cacheSet:$id:$renderName", $document);
}
?>