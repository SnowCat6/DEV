<?
function doc_read(&$db, $template, &$search)
{
	list($template, $val)  = explode(':', $template, 2);

	$fn = getFn(array(
		'doc_read_' . $template,
		'doc_read_default'
		));

	if (!$fn) return;

	$fn2 = getFn("doc_read_$template"."_before");
	if ($fn2) $fn2($db, $val, $search);

	$order		= array();
	$o			= explode(',', $search[':order']);
	$docSort	= getCacheValue('docSort');
	foreach($o as $orderName){
		$n		= $docSort[$orderName];
		if ($n) $order[]	= $n;
	}
	if ($order) $db->order	= implode(',', $order);
	else $db->order = $docSort['default'];

	$max	= (int)$search[':max'];
	if (!$max)		$max = (int)$search['max'];
	if ($max > 0)	$db->max = $max;

	//	Получить имя кеша
	$fn2		= getFn(array(
		"doc_read_$template"."_beginCache",
		"doc_read_$template"."_cache"
		));

	if ($cacheName = ($fn2?$fn2($db, $val, $search):''))
	{
		$cacheName	= hashData($cacheName);
		$cacheName	= "doc:$fn:$fn2:$cacheName";
	}

	//	Если кеш сработал, выйти
	if (!beginCache($cacheName, 'file')) return;
	ob_start();

	$sql = array();
	doc_sql($sql, $search);
	if ($sql) $db->open($sql);
	
	$search = $fn($db, $val, $search);
	$p		= ob_get_clean();
	
	if (is_array($search) && access('write', 'doc:0'))
	{
		docStartDrop($search, $template);
		echo $p;
		docEndDrop();
	}else{
		echo $p;
	}

	endCache();
}
?>
