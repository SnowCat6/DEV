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
		$docID	= NULL;
	}else{
		$docID	= (int)substr($id, 3);
	}
	
	switch($mode){
	case 'set':
		if (!$docID) return;

		$cache					= config::get('docCache', array());
		$cache[$docID][$name] 	= $ev['content'];
		config::set('docCache', $cache);
		
		$update			= config::get('docUpdate', array());
		$update[$docID] = $docID;
		config::set('docUpdate', $update);
		return;
		
	case 'get':
		if (!$docID) return;
		
		$cache	= config::get('docCache', array());
		if (isset($cache[$docID]))
			return $ev['content']	= $cache[$docID][$name];

		$data	= $db->openID($docID);
		$c		= $data?$data['cache']:NULL;
		if (!is_array($c)) $c = array();
		$cache[$docID] 	= $c;

		config::set('docCache', $cache);
		return $ev['content']	= $cache[$docID][$name];

	case 'clear':
		if ($docID)
		{
			$cache	= config::get('docCache', array());
			$cache[$docID] 	= array();
			config::set('docCache', $cache);

			$update			= config::get('docUpdate', array());
			if (!$update[$docID]) return;
			
			unset($update[$docID]);
			config::set('docUpdate', $update);

			return;
		}
		config::set('docCache', array());
		config::set('docUpdate', array());

		$db		= module('doc');
		$table	= $db->table();
		$db->exec("UPDATE $table SET `cache` = NULL, `property` = NULL");
//		m('prop:clear');
		
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
	$update			= config::get('docUpdate', array());
	if (!is_array($update)) return;

	$cache	= config::get('docCache');
	if (!is_array($cache)) return;

	//	Записать кеш документов в базу
	foreach($update as $id)
	{
		$id	= (int)$id;
		if ($id <= 0) continue;

		$db->resetCache($id);
		$d			= array();
		$d['id']	= $id;
		$d['cache']	= $cache[$id];

		$iid		= $db->update($d, false);
	}
	if ($update) $db->setValue($update, 'property', NULL);
}

//	Очистить кеш документов
//	+function doc_clear
function doc_clear($db, $id, $data)
{
	clearCache();
}

?>

<?
//	+function doc_recompile
function doc_recompile($db, $id, $data)
{
	$db->open("`searchDocument` IS NULL");
	while($data = $db->next())
	{
		$d	= array();
		$d['searchTitle']	= docPrepareSearch($data['title']);
		$d['searchDocument']= docPrepareSearch($data['document']);
		$db->setValues($db->id(), $d);
		$db->clearCache();
	}

	clearCache();
}
?>
