<?
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

	$first['action']	= 'undo:exec';
	$first['info']		= 'undo:exec_info';
	$first['data']		= $data;
	module('undo:add', $first);
}
?>