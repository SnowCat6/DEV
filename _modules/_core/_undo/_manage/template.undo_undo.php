<?
//	+function undo_add
function undo_add($db, $val, $data)
{
	global $_CONFIG;

	if ($_CONFIG[':lockUndo']) return;;
	if ($_CONFIG[':undo'])
	{
		$_CONFIG[':undo_data'][]	= $data;
		return;
	}

	$d	= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['session']	= sessionID;
	$d['date']		= time();

	if (is_array($data) && $data['action']){
		$d['action'] = getUndoAction()=='undo'?'redo':'undo';
	}
	
	$d['message']	= $data['message'];
	$d['source']	= $data['source'];
	$d['data']		= $data;

	$db->update($d);
}

//	+function undo_exec
function undo_exec($db, $val, $data)
{
	if (!access('write', 'undo')) return;
	
	beginUndo();
	foreach($data as $undo)
	{
		module($undo['action'], $undo['data']);
	}
	endUndo();
	return true;
}
//	+function undo_undo
function undo_undo($db, $id, $action)
{
	if (!access('write', 'undo')) return;
	
	$data	=$db->openID($id);
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
	if (!$undo || !$undo['action']) return;

	global $_CONFIG;
	$_CONFIG[':undoAction']	= $action;
	$bOK	= module($undo['action'], $undo['data']);
	$_CONFIG[':undoAction']	= '';
	
	if (!$bOK)
		return "Неудачная отмена действия '$undo[action]'";

	$clean	= $undo['clean'];
	if ($clean) module($clean, $undo['data']);
	
	$data['action']		= '';
	$data['message']	= "$action: $data[message]";
	$data['data']		= array();
	$db->setValues($id, $data);
	return "Отмена действия $data[message]";
}
?>