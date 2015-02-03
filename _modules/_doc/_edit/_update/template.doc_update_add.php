<?
function doc_update_add($db, $id, $data)
{
	list($id, $action, $type) = explode(':', $id, 3);
	$id			= (int)$id;

	$d			= array();
	$baseData	= array();
	$baseData['doc_type']	= $type;

	docPrepareData($db, $baseData, $data, $d);

	$error	= docBeforeUpdate($db, $action, $baseData, $data, $d);
	if ($error) return module('message:error', $error);
	
	//	Заголовок
	if (!$type)			return module('message:error', 'Неизвестный тип документа');
	if (!$d['title'])	return module('message:error', 'Нет заголовка документа');
	
	if ($id){
		if (!access('add', "doc:$id:$type"))
			return module('message:error', 'Нет прав доступа на добавление');
	}else{
		if (!access('add', "doc:$type"))
			return module('message:error', 'Нет прав доступа на добавление типа документа');
	}

	$d['user_id']	= userID();
	$iid			= $db->update($d);
	if (!$iid){
		$error = $db->error();
		logData("Error add document, $error", 'doc');
		return module('message:error', "Ошибка добавления документа в базу данных, $error");
	}
	if ($id){
		$data[':property'][':parent'] = $id;
	}
	
	//	Корекция путей в новый фолдер
	$ddb		= module('doc');
	//	Получить пути к файлам, сарый и новый
	$oldPath	= $ddb->folder();
	$newPath	= $ddb->folder($iid);
	//	Переместить все файлы в новую папку
	rename($oldPath, $newPath);
	//	Поправить документ, если он есть
	//	Компиляция, по сути можно просто обнулить, но пусть будет
	if (isset($data['document'])){
		//	Скорректировать путь к папкам
		$oldPath		= trim(str_replace(localRootPath, '', $oldPath), '/');
		$newPath		= trim(str_replace(localRootPath, '', $newPath), '/');
		//	Сделать замену старого пути на новый
		$maskedOldPath	= preg_quote($oldPath, '#');
		$document		= $data['document'];
		$document		= preg_replace("#([\"'])($maskedOldPath/)#", "\\1$newPath/", $document);
		//	Обновить документ
		$d2					= array();
		$d2['id']			= $iid;
		$d2['document']		= $document;
		$d2['searchDocument']= docPrepareSearch($document);
		$d2['cache']		= array();
		$db->update($d2);
	}
	
	beginUndo();
	addUndo("\"$d[title]\" $iid добавлен", "doc:$iid",
		array('action' => "doc:update:$iid:delete")
	);
	docAfterUpdate($db, $iid, $data);
	endUndo();

	return $iid;
}
?>
