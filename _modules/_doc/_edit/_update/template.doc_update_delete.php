<?
function doc_update_delete($db, $id, $data)
{
	list($id, $action, $type) = explode(':', $id, 3);
	$id			= (int)$id;
	$baseData	= $db->openID($id);
	if (!$baseData)
		return module('message:error', 'Нет документа');

	if (!access('delete', "doc:$id"))
		return module('message:error', 'Нет прав доступа на удаление');

	beginUndo();
	addUndo("\"$baseData[title]\" $id удален", "doc:$id",
		array('action' => "doc:undo_delete:$id", 'data' => $baseData)
	);

	event("doc.update:$action", $baseData);
	
	$url = "/page$id.htm";
	module("links:delete:$url");
	module("prop:delete:$id");
	
	$folder	= $db->folder($id);
	module('file:unlink', $folder);
	$db->delete($id);
	endUndo();

	clearCache();
	module('message', 'Документ удален');

	return true;
}
?>
