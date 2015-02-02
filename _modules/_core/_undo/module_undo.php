<?
function module_undo($val, &$data)
{
	$db	= new dbRow('log_tbl', 'log_id');
	if (!$val) return $db;
	
	list($fn, $val)	= explode(':', $val, 2);
	$fn	= getFn("undo_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function logData($message, $source = '')
{
	$data	= array(
		'message'	=> $message,
		'source'	=> $source
	);
	module("undo:add", $data);
}
function addUndo($message, $source, $data)
{
	$data['message']	= $message;
	$data['source']		= $source;
	module("undo:add", $data);
}
function getUndoAction(){
	global $_CONFIG;
	return $_CONFIG[':undoAction'];
}
function lockUndo(){
	global $_CONFIG;
	$_CONFIG[':lockUndo'] += 1;
}
function unlockUndo(){
	global $_CONFIG;
	$_CONFIG[':lockUndo'] -= 1;
}
function beginUndo()
{
	global $_CONFIG;
	
	if ($_CONFIG[':undo'] == 0){
		$_CONFIG[':undo_data']	= array();
	}
	$_CONFIG[':undo'] += 1;
 }
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
function module_undoAccess($action, $data)
{
	switch($action){
	case 'delete':
		return hasAccessRole('admin,developer');
	}
	return hasAccessRole('admin,developer,writer');
}
?>