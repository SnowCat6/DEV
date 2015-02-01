<?
//	+function module_logAdminUndo
function module_logAdminUndo($val, $data)
{
	if (!access('write', 'undo')) return;
	
	beginUndo();
	foreach($data as $pack)
	{
		$undo	= $pack['data'];
		if ($undo) module($undo['action'], $undo['data']);
	}
	endUndo();
	return true;
}
//	+function module_logUndo
function module_logUndo($val, $id)
{
	if (!access('write', 'undo')) return;
	
	$db	= new dbRow('log_tbl', 'log_id');

	$data	=$db->openID($id);
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
	if (!$undo || !$undo['action']) return;

	setUndoAction($action);
	$bOK	= module($undo['action'], $undo['data']);
	setUndoAction('');
	
	if ($bOK)
	{
		$clean	= $undo['clean'];
		if ($clean) module($clean, $undo['data']);
		
		$data['action']		= '';
		$data['message']	= "$action: $data[message]";
		$data['data']		= array();
		$db->setValues($id, $data);
		return "Отмена действия $data[message]";
	}
	return "Неудачная отмена действия '$undo[action]'";
}
?>