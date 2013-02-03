<?
function doc_page(&$db, $val, &$data)
{
	if ($val){
		//	Обработка ручного вывода
		$search	= $data;
		@list($id, $template) = explode(':', $val);
		if ($id) $search['id']	= $id;
	}else{
		//	Обработка перехода по ссылке
		$search = array();
		$search['id']	= (int)$data[1];
	}
	
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;
	
	$db->open($sql);
	while($data	= $db->next())
	{
		$id = $db->id();
		$fn = getFn("doc_page_$template");
		if (!$fn) $fn = getFn('doc_page_default');
		if ($fn) $fn($db, doc_menu($id, $data), &$data);
	}
}
?>
