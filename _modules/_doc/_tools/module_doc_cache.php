<?
//	+function doc_storage
function doc_storage($db, $mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];
	
	if (strncmp($id, 'doc', 3)) return;
	
	$docID	= (int)substr($id, 3);
	$data	= $db->openID($docID);
	if (!$data) return;

	switch($mode){
	case 'set':
		$d	= $data['fields']['any'][':storage'];
		if (!$d) $d = array();
		$d[$name]	= $ev['content'];
		
		$data	= array();
		$data['fields']['any'][':storage']	= $d;
		$bOK	=  m("doc:update:$docID:edit", $data) != 0;
		return $bOK;
	case 'get':
		$ev['content']	= $data['fields']['any'][':storage'][$name];
		return true;
	}
}

//	+function doc_cache
function doc_cache($db, $mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];

	if (strncmp($id, 'doc', 3)){
		if ($id || $mode != 'clear' || defined('docCacheClear')) return;
		define('docCacheClear', true);
		return m("doc:clear");
	}
	$docID	= (int)substr($id, 3);

	switch($mode){
	case 'set':
		$cache	= config::get('docCache', array());
		$cache[$docID][$name] 	= $ev['content'];
		config::set('docCache', $cache);
		return;
		
	case 'get':
		$cache	= config::get('docCache', array());
		if (isset($cache[$docID][$name]))
			return $ev['content']	= $cache[$docID][$name];

		$data	= $db->openID($docID);
		if (!$data) return NULL;
		
		$val	= isset($data['cache'][$name])?$data['cache'][$name]:NULL;
		$cache[$docID][$name] 	= $val;
		config::set('docCache', $cache);
		return $ev['content']	= $val;

	case 'clear':
		$cache	= config::get('docCache', array());
		$cache[$docID] 	= array();
		config::set('docCache', $cache);
		
		$cache	= config::get('docCacheClean', array());
		$cache[$docID] 	= array();
		config::set('docCacheClean', $cache);
		return;
	}
}

function doc_cacheGet($db, $id, &$data)
{
	list($id, $name) = explode(':', $id, 2);
	return getCache($name, "doc$id");
}

function doc_cacheSet($db, $id, &$cacheData)
{
	list($id, $name) = explode(':', $id, 2);
	return setCache($name, $cacheData, "doc$id");
}
function doc_cacheClear($db, $id, &$cacheData)
{
	return clearCache('', "doc$id");
}
function document($data, $fx = '')
{
	if (!beginCompile($data, '[document]')) return;

	if ($fx) show(module("text:$fx", $data['document']));
	else show($data['document']);
	
	endCompile();
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	$id		= $data['doc_id'];
	return beginCache(userID()?'':$renderName, "doc$id");
}
//	Конец кеширования компилированной версии 
function endCompile()	{ return endCache();}
function cancelCompile(){ return cancelCache(); }


function doc_cacheFlush($db, $val, $data)
{
	//	Идентификаторы документов для обновления
	$update	= config::get('doc_update');
	//	Сделать идентификаторы
	$ids	= makeIDS($update);
	//	Если документы есть, сбросить кеш
	if ($ids){
		m("doc:recompile:$ids");
		m('prop:clear');
		clearCache();
	}
	
	$cacheClean	= config::get('docCacheClean');
	if ($cacheClean && is_array($cacheClean))
	{
		$ids	= makeIDS(array_keys($cacheClean));
		$table	= $db->table();
		$key	= $db->key();
		$db->exec("UPDATE $table SET `cache` = NULL WHERE $key IN ($ids)");
		clearCache();
		return;
	}

	$cache	= config::get('docCache');
	if (!is_array($cache)) return;

	//	Записать кеш документов в базу
	foreach($cache as $id => $c)
	{
		$db->resetCache($id);
		if ($update[$id]) continue;
		
		$data		= $db->openID($id);
		if (!$data) continue;
		
		$d			= array();
		$d['id']	= $id;
		$d['cache']	= $data['cache'];
		
		foreach($c as $name => $val) $d['cache'][$name] = $val;
		$iid		= $db->update($d, false);
	}
}

?>