<?
function doc_update_edit($db, $id, $data)
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

	if (!access('write', "doc:$id"))
		return module('message:error', 'Нет прав доступа на изменение');

	if (isset($d['title']) && !$d['title'])
		return module('message:error', 'Нет заголовка документа');
	
	if (isset($data['document'])){
		$d['document']		= $data['document'];
		$d['searchDocument']= docPrepareSearch($data['document']);
	}
	$d['cache']	= array();
	$iid		= $db->setValues($id, $d);
	if (!$iid)
	{
		$error = $db->error();
		logData("Error update document $id, $error", "doc:$id");
		return module('message:error', "Ошибка добавления документа в базу данных, $error");
	}
	$db->clearCache($iid);
	
	beginUndo();
	addUndo("\"$baseData[title]\" $id изменен", "doc:$id",
		array('action' => "doc:undo_edit:$id", 'data' => $baseData)
	);
	docAfterUpdate($db, $id, $data);
	endUndo();

	return $id;
}
?>
