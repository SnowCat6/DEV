<?
module('script:lightbox');
?>
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
function module_doc_access($mode, $data){
	$id = (int)$data[1];
	switch($mode){
		case 'read': 
			return true;
		case 'delete':
		case 'write':
			@$user = $GLOBALS['_CONFIG']['user']['data'];
			return @$user['access'] == 'admin';
	}
}
function module_doc_add_access($mode, $data){
	@$user = $GLOBALS['_CONFIG']['user']['data'];
	return @$user['access'] == 'admin';
}
?>