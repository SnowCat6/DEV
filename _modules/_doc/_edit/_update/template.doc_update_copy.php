<?
function doc_update_copy($db, $id, $data)
{
	list($id, $action, $type) = explode(':', $id, 3);
	$id	= (int)$id;

	$baseData	= $db->openID($id);
	if (!$baseData)
		return module('message:error', 'Нет документа');

	$d	= array();
	docPrepareData($db, $baseData, $data, $d);
	
	$error	= docBeforeUpdate($db, $action, $baseData, $data, $d);
	if ($error) return module('message:error', $error);

	if (!access('add', "doc:$baseData[doc_type]"))
		return module('message:error', 'Нет прав доступа на добавление');

	//	Создать документ
	$d['user_id']	= userID();
	$iid			= $db->update($d);
	if (!$iid){
		$error = $db->error();
		logData("Error copy document, $error", 'doc');
		return module('message:error', "Ошибка добавления документа в базу данных, $error");
	}
	
	$d		= $db->openID($iid);
	$type	= $data['doc_type'];
	
	//	Скорректировать пути к новым файлам, скопировать файлы в новую локацию
	$oldPath= $db->folder($id);
	$newPath= $db->folder($iid);
	if (is_dir($oldPath)){
		copyFolder($oldPath, $newPath);
	}
	//	Скорректировать пути к файлам
	$d2			= array();
	$oldPath2	= str_replace(localRootPath.'/', '', $oldPath.'/');
	$newPath2	= str_replace(localRootPath.'/', '', $newPath.'/');
	$maskPath	= preg_quote($oldPath2, '#');
	$d2['document']	= $data['document']?$data['document']:$baseData['document'];
	$d2['document']	= preg_replace("#([\"\'])$oldPath2#", "\\1$newPath2", $d2['document']);

	//	Обновить документ
	$d['searchDocument']= docPrepareSearch($d2['document']);
	$d2['cache']		= array();
	$d2['id']			= $iid;
	$db->update($d2);

	beginUndo();
	addUndo("\"$d[title]\" $iid скопирован", "doc:$iid",
		array('action' => "doc:update:$iid:delete")
	);
	docAfterUpdate($db, $iid, $data);
	endUndo();
	
	return $iid;
}
?>
