<?
function doc_read(&$db, $template, &$search)
{
	list($template, $val)  = explode(':', $template, 2);

	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');
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
	$cacheName	= NULL;	
	$fn2		= getFn("doc_read_$template"."_beginCache");
	if ($fn2){
		$cacheName = $fn2($db, $val, $search);
		if ($cacheName) $cacheName = "doc:$fn2:$cacheName";
	}
	//	Если кеш сработал, выйти
	if (!memBegin($cacheName)) return;
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

	memEnd();
}
?>
