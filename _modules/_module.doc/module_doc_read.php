<?
function doc_read(&$db, $template, &$search)
{
	list($template, $val)  = explode(':', $template, 2);
	$noCache	= getNoCache();

	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	$fn2 = getFn("doc_read_$template"."_before");
	if ($fn2) $fn2($db, $val, $search);

	$order		= array();
	$o			= explode(',', $search[':order']);
	$docSort	= getCacheValue('docSort');
	foreach($o as $orderName){
		$n		= $docSort[$orderName];
		if ($n) $order[]	= $n;
	}
	if (!$order && $docSort['default']) $order[] = $docSort['default'];
	if (!$order) $order[] = '`sort`, `datePublish` DESC';
	$db->order	= implode(',', $order);

	$max		= (int)$search[':max'];
	if (!$max)	$max = (int)$search['max'];
	if ($max > 0) $db->max = $max;

	$cacheName	= NULL;	
	$fn2		= getFn("doc_read_$template"."_beginCache");

	if ($fn2) $cacheName = $fn2($db, $val, $search);
	if ($cacheName) $cacheName = "doc:read:$template:$cacheName";
	if (!memBegin($cacheName)) return;

	$sql = array();
	doc_sql($sql, $search);

	if ($sql) $db->open($sql);
	
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
	if ($fn2) $fn2($db, $val, $search);

	if (getNoCache() == $noCache) memEnd();
	else memEndCancel();

	return $db->rows();
}
?>
