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
function docType($type, $n = 0){
	$docTypes	= getCacheValue('docTypes');
	$names		= explode(':',  $docTypes[$type]);
	return @$names[$n];
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName = '')
{
	$rendered = &$data['document'];
	if (!is_array($rendered)){
		@$rendered = unserialize($rendered);
		if (!is_array($rendered)) $rendered = array();
	}

	@$compiled = $rendered[$renderName];
	if ($compiled){
		echo $compiled;
		return false;
	}

	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName = ''){
	$document	= ob_get_clean();
	event('document.compile', &$document);
	echo $document;

	$db			= module('doc:', $data);
	$id			= $db->id();
	if (!$id) return;

	$data['document'][$renderName] = $document;
	$db->setValue($id, 'document', $data['document']);
}
?>