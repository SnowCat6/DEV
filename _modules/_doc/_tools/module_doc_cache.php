<?
//	+function doc_storage
function doc_cache($db, $mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];
	
	if (strncmp($id, 'doc', 3)) return;
	$docID	= (int)substr($id, 3);

	global $_CONFIG;

	switch($mode){
	case 'set':
		$_CONFIG['docCache'][$docID][$name] = $ev['content'];
		return;
		
	case 'get':
		$cache	= &$_CONFIG['docCache'];
		$val	= $cache[$docID][$name];
		if (!is_null($val))
			return $ev['content']	= $val;

		$data	= $db->openID($docID);
		if (!$data) return NULL;
		
		$val	= isset($data['cache'][$name])?$data['cache'][$name]:NULL;
		return $ev['content']	= $val;

	case 'clear':
		$_CONFIG['docCache'][$docID]		= array();
		$_CONFIG['docCacheClean'][$docID]	= $docID;
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
	return beginCache($renderName, "doc$id");
}
//	Конец кеширования компилированной версии 
function endCompile()
{
	return endCache();
}
function cancelCompile(){
	return cancelCache();
}




function doc_cacheFlush($db, $val, $data)
{
	global $_SETTINGS;
	//	Идентификаторы документов для обновления
	$update	= $_SETTINGS['doc_update'];
	//	Сделать идентификаторы
	$ids	= makeIDS($update);
	//	Если документы есть, сбросить кеш
	if ($ids){
		m("doc:recompile:$ids");
		m('prop:clear');
	}
	
	$cacheClean	= &$_SETTINGS['docCacheClean'];
	if ($cacheClean && is_array($cacheClean))
	{
		$ids	= makeIDS(array_keys($cacheClean));
		$table	= $db->table();
		$key	= $db->key();
		$db->exec("UPDATE $table SET `cache` = NULL WHERE $key IN ($ids)");
		clearCache();
		memClear();
		return;
	}

	$cache		= &$_SETTINGS['docCache'];
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

?>