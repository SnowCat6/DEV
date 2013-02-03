<?
function doc_read(&$db, $template, &$search)
{
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;

	$db->open($sql);
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');
	return $fn?$fn($db, $search, &$data):NULL;
}
?>
