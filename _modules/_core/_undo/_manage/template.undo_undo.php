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
		list($action, $id)	= explode(':', getUndoAction());
		$d['action'] = ($action == 'undo')?'redo':'undo';
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
//	+function undo_exec_info
function undo_exec_info($db, $val, $data)
{
	if (!access('write', 'undo')) return;
	foreach($data as $undo)
	{
		$info	= $undo['info'];
		if ($info)	$info = m($info, $undo['data']);
		if (!$info)	$info = htmlspecialchars($undo['message']);
		echo "<div>$info</div>";
	}

}
//	+function undo_undo
function undo_undo($db, $id, $action)
{
	if (!access('write', "undo:$id")) return;
	
	$data	=$db->openID($id);
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
	if (!$undo || !$undo['action']) return;

	global $_CONFIG;
	$_CONFIG[':undoAction']	= "$action:$id";
	$bOK	= module($undo['action'], $undo['data']);
	$_CONFIG[':undoAction']	= '';
	accessUpdate();

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
//	+function undo_undo_info
function undo_undo_info($db, $id, $action)
{
	if (!access('write', "undo:$id")) return;
	setTemplate('');

	$data	=$db->openID($id);
	$action	= $data['action'];
	$undo	= $action?$data['data']:NULL;
	if ($undo && $undo['info']) module($undo['info'], $undo['data']);
	else echo htmlspecialchars($data['message']);
}
?>