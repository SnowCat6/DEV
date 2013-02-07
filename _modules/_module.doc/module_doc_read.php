<?
function doc_read(&$db, $template, &$search)
{
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;

	$db->open($sql);
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	ob_start();
	$search = $fn?$fn($db, $search, &$data):NULL;
	$p = ob_get_clean();
	if (is_array($search)){
		startDrop($search, $template);
		echo $p;
		endDrop($search, $template);
	}else{
		echo $p;
	}
}
?>
