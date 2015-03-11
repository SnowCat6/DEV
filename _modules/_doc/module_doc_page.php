<?
function doc_page_url(&$db, $val, &$data)
{
	//	Обработка перехода по ссылке
	$id		= (int)$data[1];
	$data	= doc_page($db, $id, $data);
	if (!$data) return docPage404();

	currentPage($id);
	moduleEx('page:title', $data['title']);
	
	$page	= $data['fields']['page'];
	if ($page && !testValue('ajax')) setTemplate($page);

	$note	= $data['fields']['note'];
	if ($note) moduleEx("page:meta:description", $note);

	$SEO	= $data['fields']['SEO'];
	if (is_array($SEO))
	{
		$title	= $SEO['title'];
		if ($title) moduleEx('page:title:siteTitle', $title);
		
		foreach($SEO as $name => $val)
		{
			if ($name == 'title') continue;
			if ($val) moduleEx("page:meta:$name", $val);
		};
	}
}
function doc_page(&$db, $val, &$data)
{
	//	Обработка ручного вывода
	list($id, $template) = explode(':', $val);
	
	$id			= alias2doc($id);
	$db->sql	= "(`visible` = 1 OR `doc_type` = 'product')";
	$data		= $db->openID($id);

	if (!$data)	return;

	$idBase	= $id;
	$fields	= $data['fields'];
	$menu	= doc_menu($id, $data, false);

	$redirect	= $fields['redirect'];
	if ($redirect)
	{
		$id 	= alias2doc($redirect);
		$data	= $db->openID($id);
		if (!$data) return;
		
		$menu	= doc_menu($id, $data, false);
		if (access('write', "doc:$idBase"))
			$menu['Изменить оригинал#ajax'] = getURL("page_edit_$idBase");
	}
	
	$fn	= getFn(array(
		'doc_page_' . $template,
		'doc_page_' . $template . '_' . $data['template'],
		'doc_page_' . $data['doc_type']. '_' . $data['template'],
		'doc_page_' . $data['doc_type'],
		'doc_page_default_' . $data['template'],
		'doc_page_default'
	));

	ob_start();
	
	event('document.begin',	$id);
	if ($fn)	$fn($db, $menu, $data);
	event('document.end',	$id);
	
	$p				= ob_get_clean();
	$pageTemplate	= $data['fields']['any']['pageTemplate'];
	moduleEx("template:compile:$pageTemplate", $p);
	echo $p;
	
	return $data;
}
function docPage404()
{
	$content= NULL;
	$ev		= array('url' => '', 'content' => &$content);
	event('site.noPageFound', $ev);
	echo $content;
}
?>
