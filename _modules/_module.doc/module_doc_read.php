<?
function doc_read(&$db, $template, &$search)
{
	@list($template, $val)  = explode(':', $template, 2);
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;

	$order		= @$search[':order'];
	if (!$order) $order = 'sort, datePublish DESC';
	$db->order	= $order;
	
	$db->open($sql);
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	ob_start();
	$search = $fn?$fn($db, $val, $search):NULL;
	$p		= ob_get_clean();
	
	if (is_array($search) && access('write', 'doc:0')){
		startDrop($search, $template);
		echo $p;
		endDrop($search, $template);
	}else{
		echo $p;
	}
	return $db->rows();
}
?>
