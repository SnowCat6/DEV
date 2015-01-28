<?
function logData($message, $source = '', $data = '')
{
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
?>