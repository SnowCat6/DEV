<?
function logData($message, $source = '', $data = '')
{
	return;

	$db	= new dbRow('log_tbl', 'log_id');
	
	$d	= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['session']	= sessionID;
	$d['date']		= time();
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= serialize($data);
	
	foreach($d as $name => &$val) $val	= dbEncString($db, $val);
	$db->insertRow($db->table, $d, true);
}
?>