<?
function doc_page(&$db, $val, &$data)
{
	module('script:lightbox');
	module('script:ajaxLink');
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
		$id		= $db->id();
		@$fields= $data['fields'];
		@$SEO	= $fields['SEO'];
		currentPage($id);
		
		module('page:title', $data['title']);
		
		@$title = $SEO['title'];
		if (!$title) $title = $data['title'];
		module('page:title:siteTitle', $title);

		if (is_array($SEO)){
			foreach($SEO as $name => $val){
				if ($name == 'title') continue;
				module("page:meta:$name", $val);
			};
		}
	
		$fn = getFn("doc_page_$template");
		if (!$fn) $fn = getFn("doc_page_$data[doc_type]");
		if (!$fn) $fn = getFn('doc_page_default');
		if ($fn) $fn($db, doc_menu($id, $data), &$data);
	}
}
?>
