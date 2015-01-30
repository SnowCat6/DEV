<?
function logData($message, $source = '', $data = '')
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
	

	if (is_array($data)){
		if ($data['undo']['action'])	$d['action'] = 'undo';
		if ($data['redo']['action'])	$d['action'] = 'redo';
	}
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= $data;

	$db->update($d);
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
		return logData($first['message'], $first['source'], $first['data']);
		
	$info	= array();
	foreach($data as $undo) $info[]	= $undo['message'];

	$action	= $data[0]['data']['redo'];
	$action	= $action?'redo':'undo';

	$undo	= array($action => array(
		'action'=> 'logAdminUndo',
		'data' 	=> $data,
		'info'	=> $info
		));
	logData($first['message'], $first['source'], $undo);
 }
function module_logUndoAccess($action, $data){
	return hasAccessRole('admin,developer');
}
?>