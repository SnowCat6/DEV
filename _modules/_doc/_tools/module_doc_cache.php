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
	
	if (strncmp($id, 'doc', 3)) return;
	$docID	= (int)substr($id, 3);

	global $_CONFIG;

	switch($mode){
	case 'set':
		$_CONFIG['docCache'][$docID][$name] = $ev['content'];
		return;
		
	case 'get':
		$val	= $_CONFIG['docCache'][$docID][$name];
		if (is_null($val))
		{
			$data	= $db->openID($docID);
			if (!$data) return NULL;
			$val	= isset($data['cache'][$name])?$data['cache'][$name]:NULL;
		}
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
function document(&$data)
{
	if (beginCompile($data, '[document]'))
	{
		show($data['document']);
		endCompile();
	}
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	$id		= $data['doc_id'];
	return beginCache($renderName, "doc$id");
}
//	Конец кеширования компилированной версии 
function endCompile(){	return endCache();}
function cancelCompile(){ return cancelCache(); }


function doc_cacheFlush($db, $val, $data)
{
	global $_CONFIG;
	//	Идентификаторы документов для обновления
	$update	= $_CONFIG['doc_update'];
	//	Сделать идентификаторы
	$ids	= makeIDS($update);
	//	Если документы есть, сбросить кеш
	if ($ids){
		m("doc:recompile:$ids");
		m('prop:clear');
		clearCache();
	}
	
	$cacheClean	= &$_CONFIG['docCacheClean'];
	if ($cacheClean && is_array($cacheClean))
	{
		$ids	= makeIDS(array_keys($cacheClean));
		$table	= $db->table();
		$key	= $db->key();
		$db->exec("UPDATE $table SET `cache` = NULL WHERE $key IN ($ids)");
		clearCache();
		return;
	}

	$cache		= &$_CONFIG['docCache'];
	if (!is_array($cache)) return;

	//	Записать кеш документов в базу
	foreach($cache as $id => &$c)
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