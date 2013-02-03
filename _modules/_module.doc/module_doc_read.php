<?
function doc_read(&$db, $val, &$search)
{
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;
	
	$db->open($sql);
	$fn = getFn('doc_read_default');
	return $fn?$fn($db, $search, &$data):NULL;
}
?>
