<?
function doc_read(&$db, $template, &$search)
{
	@list($template, $val)  = explode(':', $template, 2);
	
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	$fn2 = getFn("doc_read_$template"."_before");
	if (!$fn2)$fn2 = getFn('doc_read_default_before');
	if ($fn2) $fn2(&$db, $val, &$search);
	
	$order		= @$search[':order'];
	if (!$order) $order = '`sort`, `datePublish` DESC';
	$db->order	= $order;
	
	@$max		= (int)$search['max'];
	if ($max > 0) $db->max = $max;
	
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;
//define('_debug_', true);
	$db->open($sql);
	
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

	$fn2 = getFn("doc_read_$template"."_after");
	if (!$fn2)$fn2 = getFn('doc_read_default_after');
	if ($fn2) $fn2(&$db, $val, &$search);

	return $db->rows();
}
?>
