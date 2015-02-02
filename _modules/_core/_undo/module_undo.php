<?
function module_undo($val, &$data)
{
	$db	= new dbRow('log_tbl', 'log_id');
	if (!$val) return $db;
	
	list($fn, $val)	= explode(':', $val, 2);
	$fn	= getFn("undo_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
//	Добавить сообщение о действия пользователя
function logData($message, $source = '')
{
	$data	= array(
		'message'	=> $message,
		'source'	=> $source
	);
	module("undo:add", $data);
}
//	Добавить слепок для отмены действия пользователя
function addUndo($message, $source, $data)
{
	$data['message']	= $message;
	$data['source']		= $source;
	module("undo:add", $data);
}
//	Получить тип текущего действия
function getUndoAction(){
	global $_CONFIG;
	return $_CONFIG[':undoAction'];
}
//	Заблокировать запись действий отмены
function lockUndo(){
	global $_CONFIG;
	$_CONFIG[':lockUndo'] += 1;
}
//	Разблокировать запись действий
function unlockUndo(){
	global $_CONFIG;
	$_CONFIG[':lockUndo'] -= 1;
}
//	Начать сбор действий в пакет
function beginUndo()
{
	global $_CONFIG;
	
	if ($_CONFIG[':undo'] == 0){
		$_CONFIG[':undo_data']	= array();
	}
	$_CONFIG[':undo'] += 1;
}
//	Окончить сбор действий, создать запись отмены
function endUndo()
{
	global $_CONFIG;
	
	$_CONFIG[':undo'] -= 1;
	if ($_CONFIG[':undo']) return;
	
	$data	= $_CONFIG[':undo_data'];
	$_CONFIG[':undo_data']	= array();
	
	$first	= $data[0];
	
	if (count($data) == 0)
		return;
	if (count($data) == 1)
		return module('undo:add', $first);
		
	$info	= array();
	foreach($data as $undo) $info[]	= $undo['message'];

	$first['action']	= 'undo:exec';
	$first['info']		= $info;
	$first['data']		= $data;
	module('undo:add', $first);
}
//	Права доступа для отмены действия
function undo_access($db, $action, $data)
{
	$id	= $data[1];
	if (!$id) list($action, $id)	= explode(':', getUndoAction());
	
	switch($action){
	case 'delete':
		return hasAccessRole('admin,developer');
	case 'read':
		if (userID()) return true;
		break;
	}
	
	if (hasAccessRole('admin,developer,writer')) return true;
	if (!$id || !userID()) return;
	
	$data	= $db->openID($id);
	return $data['user_id'] == userID();
}
?>