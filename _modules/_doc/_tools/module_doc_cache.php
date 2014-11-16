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
	if (!$name) return NULL;

	if (defined('memcache')){
		$val	= memGet("doc:$id:$name");
		if (!is_null($val)) return $val;
	}
	
	$cache	= &$GLOBALS['_CONFIG']['docCache'];
	$val	= $cache[$id][$name];
	if (!is_null($val)) return $val;

	$data = $db->openID($id);
	if (!$data) return NULL;

	return isset($data['cache'][$name])?$data['cache'][$name]:NULL;
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
function doc_cacheClear($db, $id, $cacheData)
{
	$GLOBALS['_CONFIG']['docCache'][$id]	 = array();
	$GLOBALS['_CONFIG']['docCacheClean'][$id]= $id;
	module('message:trace', "Document cache clean");
}
function doc_cacheFlush($db, $val, $data)
{
	//	Идентификаторы документов для обновления
	$update	= $GLOBALS['_SETTINGS']['doc_update'];
	//	Сделать идентификаторы
	$ids	= makeIDS($update);
	//	Если документы есть, сбросить кеш
	if ($ids){
		m("doc:recompile:$ids");
		m('prop:clear');
	}
	
	$cacheClean	= &$GLOBALS['_CONFIG']['docCacheClean'];
	if (is_array($cacheClean)){
		$ids	= makeIDS(array_keys($cacheClean));
		$table	= $db->table();
		$key	= $db->key();
		$db->exec("UPDATE $table SET `cache` = NULL WHERE $key IN ($ids)");
		clearCache();
		memClear();
		return;
	}

	$cache		= &$GLOBALS['_CONFIG']['docCache'];
	if (!is_array($cache)) return;

	//	Записать кеш документов в базу
	foreach($cache as $id => &$cache)
	{
		$db->resetCache($id);
		if ($update[$id]) continue;
		
		$data		= $db->openID($id);
		if (!$data) continue;
		
		$d			= array();
		$d['id']	= $id;
		$d['cache']	= $data['cache'];
		
		foreach($cache as $name => &$val) $d['cache'][$name] = $val;
		$iid		= $db->update($d, false);
	}
}
function getDocument(&$data){
	ob_start();
	document($data);
	return ob_get_clean();
}
function document(&$data){
	if (!beginCompile($data, '[document]')) return;
	echo $data['document'];
	endCompile();
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
	$noCache	= getNoCache();
	pushStackName("$noCache:$renderName", $data);
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile()
{
	$document	= ob_get_clean();
	
	$data		= getStackData();
	$id			= $data['doc_id'];
	$renderName	= popStackName();
	event('document.compile', $document);
	showDocument($document, $data);

	list($noCache, $renderName) = explode(':', $renderName, 2);
	if ($noCache != getNoCache()) return;
	
	module("doc:cacheSet:$id:$renderName", $document);
}
function cancelCompile(){
	$renderName	= popStackName();
	ob_end_flush();
}
?>