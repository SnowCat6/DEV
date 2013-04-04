<?
//	doc:update::add:page		=> ret id, sample 20
//	doc:update:20				= ret 20, ok
//	doc:update:20:add:page		=> ret id, new hierarhy, added page
//	doc:update:20:add:article	=> ret id, new hierarhy, added article
function doc_update(&$db, $id, &$data)
{
	list($id, $action, $type) = explode(':', $id, 3);

	$d	= array();
	$id	= (int)$id;
	if ($id){
		$baseData	= $db->openID($id);
		if (!$baseData) return module('message:error', 'Нет документа');

		@$d['fields']	= $baseData['fields'];
	}else{
		$baseData	= array();
	}

	//	Удаление
	if ($action == 'delete')
	{
		if (!access('delete', "doc:$id")) return module('message:error', 'Нет прав доступа на удаление');

		event("doc.update:$action", &$baseData);
		
		$url = "/page$id.htm";
		module("links:delete:$url");
		module("prop:delete:$id");
		$db->delete($id);
		module('message', 'Документ удален');
		return true;
	}

	if (isset($data['title'])){
		$d['title'] = $data['title'];
	}
	//	Подготовка базовый данных, проверка корректности
	//	Видимость
	if (isset($data['visible'])){
		$d['visible']	= (int)$data['visible'];
	}
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
	
	$error = NULL;
	switch($action){
		//	Добавление
		case 'add':
			if (isset($data['template'])) $d['template']	= $data['template'];
			$d['doc_type']	= $type;

			//	Заголовок
			if (isset($d['title'])){
				$d['searchTitle']	= docPrepareSearch($d['title']);
			}
			
			//	Пользовательская обработка данных
			$base = array(&$d, &$data, &$error);
			event("doc.update:$action", &$base);
			if ($error) return module('message:error', $error);
			
			if ($id){
				if (!access('add', "doc:$baseData[doc_type]:$type"))
					return module('message:error', 'Нет прав доступа на добавление');
			}else{
				if (!access('add', "doc:$type"))
					return module('message:error', 'Нет прав доступа на добавление типа документа');
			}
			if (!$type)			return module('message:error', 'Неизвестный тип документа');
			if (!@$d['title'])	return module('message:error', 'Нет заголовка документа');
	
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
				$d['searchDocument']		= docPrepareSearch($document);
				$d['document']				= array();
				$db->update($d);
			}
		break;
		//	Редактирование
		case 'edit':
			//	Пользовательская обработка данных
			$d		= $baseData;
			$base	= array(&$d, &$data, &$error);
			event("doc.update:$action", &$base);
			if ($error) return module('message:error', $error);

			//	Заголовок
			if (isset($d['title'])){
				$d['searchTitle']	= docPrepareSearch($d['title']);
			}

			if (!$baseData)							return module('message:error', 'Нет документа');
			if (!access('write', "doc:$id"))		return module('message:error', 'Нет прав доступа на изменение');
			if (isset($d['title']) && !$d['title'])	return module('message:error', 'Нет заголовка документа');
			
			if (isset($data['template']) && hasAccessRole('admin,developer'))
			{
				$d['template']	= $data['template'];
			}
			
			if (isset($data['originalDocument'])){
				$d['originalDocument']	= $data['originalDocument'];
				$d['searchDocument']	= docPrepareSearch($data['originalDocument']);
				$d['document']			= array();
			}

			$d['id']= $id;
			$iid	= $db->update($d);
			if (!$iid){
				$error = mysql_error();
				return module('message:error', "Ошибка добавления документа в базу данных, $error");
			}
			
			$d		= $db->openID($iid);
			$type	= $data['doc_type'];
		break;
		default:
			return module('message:error', "Неизвестная команда '$action'");
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

	//	Если есть родители, то обновить кеш
	$prop	= module("prop:get:$iid");
	@$parent= $prop[':parent']['property'];
	if ($parent) module("doc:recompile:$parent");
	
	return $iid;
}
?>