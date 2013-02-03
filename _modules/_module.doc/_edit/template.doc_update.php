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
	
	if ($action == 'delete')
	{
		if (!access('delete', "doc:$id")) return module('message:error', 'Нет прав доступа на удаление');
		
		$url = "/page$id.htm";
		module("links:delete:$url");
		module("prop:set:$id");
		$db->delete($id);
		module('message', 'Документ удален');
		return true;
	}

	$d = array();
	if (isset($data['title']))	$d['title'] = $data['title'];
	if (isset($data['originalDocument'])){
		$d['originalDocument']	= $data['originalDocument'];
		$d['document']			= $data['originalDocument'];
		event('document.compile', &$d['document']);
	}

	switch($action){
		case 'add':
			if (!access('add', "doc:$type")) return module('message:error', 'Нет прав доступа на добавление');
			if (!$type)			return module('message:error', 'Неизвестный тип документа');
			if (!@$d['title'])	return module('message:error', 'Нет заголовка документа');
			
			$d['doc_type']	= $type;
			$iid			= $db->update($d);
			if (!$iid) 	return module('message:error', 'Ошибка добавления документа в базу данных');
		break;
		case 'edit':
			if (!access('write', "doc:$id"))		return module('message:error', 'Нет прав доступа на изменение');
			if (isset($d['title']) && !$d['title'])	return module('message:error', 'Нет заголовка документа');
			
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

	@$prop = $data[':property'];
	if (is_array($prop)){
		if ($id){
			@$p	= module("prop:get:$id");
			foreach($p as $name => $val){
				if ($name[0] != ':') continue;
				if (isset($prop[$name])) continue;
				$prop[$name] = $val['property'];
			}
		}
		module("prop:set:$iid", $prop);
	}

	return $iid;
}
?>