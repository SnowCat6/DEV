<?
//	doc:update::add:page		=> ret id, sample 20
//	doc:update:20				= ret 20, ok
//	doc:update:20:add:page		=> ret id, new hierarhy, added page
//	doc:update:20:add:article	=> ret id, new hierarhy, added article
function doc_update(&$db, $id, &$data)
{
	list($id, $action, $type) = explode(':', $id, 3);

	$id = (int)$id;
	if ($id){
		$d = $db->openID($id);
		if (!$d) return module('message:error', 'Нет документа');
	}

	@$docTitle	= $data['title'];
	if (!$docTitle) return module('message:error', 'Нет заголовка документа');
	
	$d = array();
	$d['title']				= $docTitle;;
	$d['originalDocument']	= @$data['originalDocument'];
	$d['document']			= @$data['originalDocument'];
	event('document.compile', &$d['document']);

	switch($action){
		case 'add':
			if (!$type)	return module('message:error', 'Неизвестный тип документа');
			$d['doc_type']	= $type;
			$iid			= $db->update($d);
			if (!$iid) 	return module('message:error', 'Ошибка добавления документа в базу данных');
		break;
		case 'edit':
			$d['id']	= $id;
			$iid		= $db->update($d);
			if (!$iid) return module('message:error', 'Ошибка добавления документа в базу данных');
		break;
		default:
			return module('message:error', 'Неизвестная команда');
	}
	
	@$links = $data[':links'];
	if (is_array($links)){
		$url = "/page$iid.htm";
		module("links:delete:$url");
		foreach($links as $link){
			module("links:add:$url", $link);
		}
	}

	return $iid;
}
?>