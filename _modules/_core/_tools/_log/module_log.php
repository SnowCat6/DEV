<?
function logData($message, $source = '')
{
	addUndo($message, $source, array());
}
function addUndo($message, $source, $data)
{
	global $_CONFIG;

	if ($_CONFIG[':lockUndo']) return;;
	if ($_CONFIG[':undo'])
	{
		$_CONFIG[':undo_data'][]	= array(
			'message'	=> $message,
			'source'	=> $source,
			'data'		=> $data
			);
		return;
	}

	$db	= new dbRow('log_tbl', 'log_id');
	
	$d	= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['session']	= sessionID;
	$d['date']		= time();

	if (is_array($data) && $data['action']){
		$d['action'] = getUndoAction()=='undo'?'redo':'undo';
	}
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= $data;

	$db->update($d);
}
function setUndoAction($action){
	global $_CONFIG;
	$_CONFIG[':undoAction']	= $action;
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
		return addUndo($first['message'], $first['source'], $first['data']);
		
	$info	= array();
	foreach($data as $undo) $info[]	= $undo['message'];

	addUndo($first['message'], $first['source'], array(
		'action'=> 'logAdminUndo',
		'data' 	=> $data,
		'info'	=> $info
		)
	);
 }
function module_logUndoAccess($action, $data){
	return hasAccessRole('admin,developer');
}
?>