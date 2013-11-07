<?
function doc_getPageCacheName($db, &$val, &$pageCacheName)
{
//	if (userID() || !is_null($pageCacheName)) return;
//	$cache			= &$GLOBALS['_CACHE'];
//	$pageCacheName	= $cache['docFullPageCache'][getRequestURL()];
}
function doc_cacheGet($db, $id, $data)
{
	list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;

	if (defined('memcache')){
		$val	= memGet("doc:$id:$name");
		if (!is_null($val)) return $val;
	}
	
	$val	= $GLOBALS['_CONFIG']['docCache'];
	$val	= $val[$id][$name];
	if (!is_null($val)) return $val;
	
	$data = $db->openID($id);
	if (!$data) return;

	return $data['document'][$name];
}

function doc_cacheSet($db, $id, $cacheData)
{
	list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;

	if (defined('memcache')){
		memSet("doc:$id:$name", $cacheData);
		module('message:trace', "Document memcache set, $id => $name");
	}
	
	$GLOBALS['_CONFIG']['docCache'][$id][$name] = $cacheData;
	module('message:trace', "Document cache set, $id => $name");
}
function doc_cacheFlush($db, $val, $data)
{
	$cache		= &$GLOBALS['_CONFIG']['docCache'];
	if (!is_array($cache)) return;

	foreach($cache as $id => &$cache)
	{
		$data		= $db->openID($id);
		if (!$data) continue;
		
		$d				= array();
		$d['id']		= $id;
		$d['document']	= $data['document'];
		
		foreach($cache as $name => &$val) $d['document'][$name] = $val;
		$iid			= $db->update($d);
	}
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
	if (!is_null($cache)){
		showDocument($cache, $data);
		return false;
	}

	ob_start();
	pushStackName("doc:$id", $renderName);
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName = NULL)
{
	$id			= $data['doc_id'];
	$renderName	= popStackName("doc:$id");
	
	$document	= ob_get_clean();
	event('document.compile', $document);
	showDocument($document, $data);
	if (!localCacheExists()) return;
	module("doc:cacheSet:$id:$renderName", $document);
}
?>