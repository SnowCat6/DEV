<? function doc_cacheSet($db, $id, $cacheData)
{
	@list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;
	
	$d	= array();
	$d['id']				= $id;
	$d['document']			= $data['document'];
	$d['document'][$name]	= $cacheData;
	$iid= $db->update($d);
	$id	= $db->id();
	if (!$iid){
		module('message:trace:error', "Cache not set, $name");
		return;
	}

	$db->resetCache($id);
	module('message:trace', "Document cache set, $id => $name");
}
?>
<? function doc_cacheGet($db, $id, $data)
{
	@list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;
	
	@$cache	= $data['document'][$name];
	return $cache;
}
?>
<?
function document(&$data){
	if (!beginCompile(&$data, 'document')) return;
	echo $data['originalDocument'];
	endCompile(&$data, 'document');
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	if (localCacheExists())
	{
		$id		= $data['doc_id'];
		$cache	= module("doc:cacheGet:$id:$renderName");
		if (isset($cache)){
			showDocument($cache, $data);
			return false;
		}
	}
	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName)
{
	$id			= $data['doc_id'];
	$document	= ob_get_clean();
	event('document.compile', &$document);
	showDocument($document, $data);
	if (!localCacheExists()) return;
	
	module("doc:cacheSet:$id:$renderName", $document);
}
?>