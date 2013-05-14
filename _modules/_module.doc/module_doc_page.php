<?
function doc_page(&$db, $val, &$data)
{
	if ($val != 'url'){
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
		
		if ($val == 'url')
		{
			@$SEO	= $fields['SEO'];
			currentPage($id);
			
			module('page:title', $data['title']);
			
			@$title = $SEO['title'];
			if ($title)
				module('page:title:siteTitle', $title);
	
			if (is_array($SEO)){
				foreach($SEO as $name => $val){
					if ($name == 'title') continue;
					module("page:meta:$name", $val);
				};
			}
		}
		
		$fn = getFn("doc_page_$template");
		if (!$fn)	$fn = getFn("doc_page_$template".		"_$data[template]");

		if (!$fn)	$fn = getFn("doc_page_$data[doc_type]".	"_$data[template]");
		if (!$fn)	$fn = getFn("doc_page_$data[doc_type]");

		if (!$fn)	$fn = getFn('doc_page_default'.			"_$data[template]");
		if (!$fn)	$fn = getFn('doc_page_default');

		event('document.begin',	$id);
		if ($fn)	$fn($db, doc_menu($id, $data, false), &$data);
		event('document.end',	$id);
	}
}
?>
