<?
function logData($message, $source = '', $data = '')
{
	global $_CONFIG;
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
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= $data;

	$db->update($d);
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

	$undo	= array('undo' => array('action' => 'logAdminUndo', 'data' => $data));
	logData($first['message'], $first['source'], $undo);
 }
function module_logUndoAccess($action, $data){
	return hasAccessRole('admin,developer');
}
?>