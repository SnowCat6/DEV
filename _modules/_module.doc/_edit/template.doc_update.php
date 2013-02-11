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
		$baseData	= $db->openID($id);
		if (!$baseData) return module('message:error', 'Нет документа');
	}else{
		$baseData	= array();
	}

	//	Удаление
	if ($action == 'delete')
	{
		if (!access('delete', "doc:$id")) return module('message:error', 'Нет прав доступа на удаление');
		
		$url = "/page$id.htm";
		module("links:delete:$url");
		module("prop:delete:$id");
		$db->delete($id);
		module('message', 'Документ удален');
		return true;
	}

	//	Подготовка базовый данных, проверка корректности
	$d = array();
	//	Заголовок
	if (isset($data['title']))		$d['title'] = $data['title'];
	//	Sime abstract local fields
	if (isset($data['fields'])){
		//	SEO fields
		if (is_array($data['fields']['SEO']) && hasAccessRole('admin,developer,SEO')){
			$d['fields']['SEO'] = $data['fields']['SEO'];
		}
	}

	//	Дата публикации
	if (isset($data['datePublish']))
	{
		if ($data['datePublish']){
			$d['datePublish'] = makeSQLDate(makeDateStamp($data['datePublish']));
		}else{
			$d['datePublish'] = NULL;
		}
	}
	if (isset($data['price']))
	{
		if (isset($d['fields'])) @$d['fields'] = $baseData['fields'];
		
		$price = (float)$data['price'];
		$d['fields']['price']['base']	= $price;
		$data['fields']['price']['base']= $price;

		compilePrice(&$data, $false);
	}

	switch($action){
		//	Добавление
		case 'add':
			if ($id){
				if (!access('add', "doc:$baseData[doc_type]:$type"))
					return module('message:error', 'Нет прав доступа на добавление');
			}else{
				if (!access('add', "doc:$type"))
					return module('message:error', 'Нет прав доступа на добавление');
			}
			if (!$type)			return module('message:error', 'Неизвестный тип документа');
			if (!@$d['title'])	return module('message:error', 'Нет заголовка документа');

	
			$d['doc_type']	= $type;
			$iid			= $db->update($d);
			if (!$iid) 	return module('message:error', 'Ошибка добавления документа в базу данных');
			if ($id) 	$data[':property'][':parent'] = $id;
			
			//	Корекция путей в новый фолдер
			$ddb		= module('doc');
			//	Получить пути к файлам, сарый и новый
			$oldPath	= $ddb->folder();
			$newPath	= $ddb->folder($iid);
			//	Переместить все файлы в новую папку
			@rename($oldPath, $newPath);
			//	Поправить документ, если он есть
			//	Компиляция, по сути можно просто обнулить, но пусть будет
			if (isset($data['originalDocument'])){
				//	Скорректировать путь к папкам
				$oldPath		= trim(str_replace(localHostPath, '', $oldPath), '/');
				$newPath		= trim(str_replace(localHostPath, '', $newPath), '/');
				//	Сделать замену старого пути на новый
				$maskedOldPath	= preg_quote($oldPath, '#');
				$document		= $data['originalDocument'];
				$document		= preg_replace("#([\"'])($maskedOldPath/)#", "\\1$newPath/", $document);
				//	Обновить документ
				$d							= array();
				$d['id']					= $iid;
				$d['originalDocument']		= $document;
				$d['document']				= array();
				$db->update($d);
			}
			echo $oldPath, ' - ',$newPath;die;
		break;
		//	Редактирование
		case 'edit':
			if (!access('write', "doc:$id"))		return module('message:error', 'Нет прав доступа на изменение');
			if (isset($d['title']) && !$d['title'])	return module('message:error', 'Нет заголовка документа');
			
			if (isset($data['originalDocument'])){
				$d['originalDocument']	= $data['originalDocument'];
				$d['document']				= array();
			}

			$d['id']= $id;
			$iid	= $db->update($d);
			if (!$iid) return module('message:error', 'Ошибка добавления документа в базу данных');
			
			$d		= $db->openID($iid);
			$type	= $data['doc_type'];
		break;
		default:
			return module('message:error', 'Неизвестная команда');
	}
	
	//	Обновить ссылки на документ
	@$links = $data[':links'];
	if (is_array($links)){
		$url = "/page$iid.htm";
		module("links:delete:$url");
		foreach($links as $link){
			module("links:add:$url", $link);
		}
	}

	//	Записать свойства, если имеются
	@$prop = $data[':property'];
	if (is_array($prop)){
		module("prop:set:$iid", $prop);
	}

	//	Pre compile, ipdate price property
	if (beginCompile(&$data, 'document'))
		endCompile(&$data, 'document');
	
	//	Если есть родители, то обновить кеш
	$prop	= module("prop:get:$iid");
	@$parent= $prop[':parent']['property'];
	if ($parent) module("doc:recompile:$parent");

	return $iid;
}
?>