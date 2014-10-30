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
//	if (!$order) $order[] = '`sort`, `datePublish` DESC';
	$db->order	= implode(',', $order);

	$max		= (int)$search[':max'];
	if (!$max)	$max = (int)$search['max'];
	if ($max > 0) $db->max = $max;

	$cacheName	= NULL;	
	if (defined('memcache'))
	{
		$fn2		= getFn("doc_read_$template"."_beginCache");
		if ($fn2) $cacheName = $fn2($db, $val, $search);
		if ($cacheName) $cacheName = "doc:$fn2:$cacheName";
	}
	if (!memBegin($cacheName)) return;

	$sql = array();
	doc_sql($sql, $search);

	if ($sql) $db->open($sql);
	
	ob_start();
	$search = $fn?$fn($db, $val, $search):NULL;
	$p		= ob_get_clean();
	
	if (is_array($search) && access('write', 'doc:0'))
	{
		if ($search[':sortable'])
		{
			if (!isset($search[':sortable']['action']))		$search[':sortable']['action']		= 'ajax_edit.htm?ajax=itemSort';
			if (!isset($search[':sortable']['itemFilter']))	$search[':sortable']['itemFilter']	= '.adminEditMenu a[href*=page_edit]';
			if (!isset($search[':sortable']['itemData']))	$search[':sortable']['itemData']	= 'href';
		}
		startDrop($search, $template, false, docDropAccess($search['type'], $search['template']));
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
