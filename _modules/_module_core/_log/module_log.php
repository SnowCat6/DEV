<?
function logData($message, $source = '', $data = '')
{
	$db	= new dbRow('log_tbl', 'log_id');
	
	$d	= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['session']	= sessionID;
	$d['date']		= makeSQLDate(time());
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= serialize($data);
	
	foreach($d as $name => &$val) makeSQLValue($val);
	$db->insertRow($db->table, $d, true);
}
?>