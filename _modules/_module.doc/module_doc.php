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
		module('message:error', "Document not compiled, $renderName");
		return;
	}
	module('message:trace', "Document compiled, $id => $renderName");

	$data['document'][$renderName] = $document;
	$db->setValue($id, 'document', $data['document'], false);
}
function doc_recompile($db, $id, $data){
	$ids = makeIDS($ids);
	if ($ids){
		$db->setValue($id, 'document', NULL, false);
	}else{
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
	}
}
?>