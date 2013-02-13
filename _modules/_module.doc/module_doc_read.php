<?
function doc_read(&$db, $template, &$search)
{
	@list($template, $val)  = explode(':', $template, 2);

	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;

	$db->open($sql);
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	ob_start();
	$search = $fn?$fn($db, $val, $search):NULL;
	$p = ob_get_clean();
	if (is_array($search) && hasScriptUser('draggable')){
		startDrop($search, $template);
		echo $p;
		endDrop($search, $template);
	}else{
		echo $p;
	}
}
?>
