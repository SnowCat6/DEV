<?
//	+function module_undo
function module_undo($val, &$data)
{
	if (!$val) return undo::db();
	
	list($fn, $val)	= explode(':', $val, 2);
	$fn	= getFn("undo_$fn");
	return $fn?$fn($val, $data):NULL;
}

//	+function undo_exec
function undo_exec($val, $data)
{
	if (!access('write', 'undo')) return;
	
	undo::begin();
	foreach($data as $undo){
		module($undo['action'], $undo['data']);
	}
	undo::end();
	return true;
}
//	+function undo_exec_info
function undo_exec_info($val, $data)
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
//	Права доступа для отмены действия
//	+function undo_access
function undo_access($action, $data)
{
	$id	= $data[1];
	if (!$id) list(, $id)	= explode(':', undo::getUndoAction());

	switch($action){
	case 'delete':
		return hasAccessRole('admin,developer');
	case 'read':
		if (userID()) return true;
		break;
	}
	
	if (hasAccessRole('admin,developer,writer')) return true;
	if (!$id || !userID()) return;
	
	$db		= undo::db();
	$data	= $db->openID($id);
	return $data['user_id'] == userID();
}
?>