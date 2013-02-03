<?
function doc_page(&$db, $val, &$data)
{
	if ($val){
		//	Обработка ручного вывода
		@list($id, $template) = explode(':', $val);
		$search	= $data;
	}else{
		//	Обработка перехода по ссылке
		$search = array();
		$search['id']	= (int)$data[1];
	}
	
	$sql = array();
	doc_sql($sql, $search);
	
	$db->open($sql);
	while($data	= $db->next())
	{
		$fn = getFn("doc_page_$template");
		if (!$fn) $fn = getFn('doc_page_default');
		if ($fn) $fn($db, doc_menu($id, $data), &$data);
	}
}
?>
