<?
function doc_page_url(&$db, $val, &$data)
{
	//	Обработка перехода по ссылке
	$id		= (int)$data[1];
	return docPageEx($db, $id, $data, true);	
}
function doc_page(&$db, $val, &$data)
{
	return docPageEx($db, $val, $data, false);	
}

function docPageEx(&$db, $val, &$data, $bThisPage){
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
	
	if ($bThisPage)
	{
		currentPage($id);
		moduleEx('page:title', $data['title']);
		
		$page	= $data['fields']['page'];
		if ($page && !testValue('ajax')) setTemplate($page);

		module('SEO:set', doc_SEOget($db, $id, $data));
	}
	
	$fn	= getFn(array(
		'doc_page_' . $template,
		'doc_page_' . $template . '_' . $data['template'],
		'doc_page_' . $data['doc_type']. '_' . $data['template'],
		'doc_page_' . $data['doc_type'],
		'doc_page_default_' . $data['template'],
		'doc_page_default'
	));

	event('document.begin',	$id);
	if ($fn)	$fn($db, $menu, $data);
	event('document.end',	$id);

	return $data;
}
function docPage404()
{
	$content= NULL;
	$ev		= array('url' => '', 'content' => &$content);
	event('site.noPageFound', $ev);
	echo $content;
}
//	+function doc_SEO
function doc_SEOget($db, $id, $data)
{
	$SEO_data			= getCache("SEO_data", "doc$id");
	if (!is_array($SEO_data))
	{
		$SEO_data['title']	= $data['title'];

		$parents	= getPageParents($id, true);
		for($ix = 0; $ix < 3; ++$ix)
		{
			$rootID		= $parents[$ix];
			if (!$rootID) continue;

			$d	= $db->openID($rootID);
			if ($ix){ $i = $ix+1; $name = "root$i"; }
			else $name = "root";
			
			$SEO_data[$name]	= $d['title'];
		}
		$parentID	= $parents[count($parents)-1];
		if ($parentID){
			$d	= $db->openID($parentID);
			$SEO_data['parent']	= $d['title'];
		}
	
		$props	= module("prop:get:$id:productSEO");
		$peop	= array();
		$keys	= array();
		foreach($props as $name => $val)
		{
			$SEO_data[$name]	= $val;
			$prop[]				= "$name: $val";
			$keys[]				= $val;
		}
		if ($prop){
			$SEO_data['property']	= implode(', ', $prop);
			$SEO_data['keywords']	= implode(', ' ,$keys);
		}
		setCache("SEO_data", $SEO_data, "doc$id");
	}
	
	$ini		= getIniValue(':SEO_doc');
	//	Страницы типа
	$type		= $data['doc_type'];
	$template	= $data['template'];

	$SEO1	= getStorage("SEO_$type", 'ini'); 
	removeEmpty($SEO1);
	$SEO2	= getStorage("SEO_$type"."_$template", 'ini');
	removeEmpty($SEO2);
	$SEO3	= $data['fields']['SEO'];
	removeEmpty($SEO3);
	
	$SEO	= array();
	dataMerge($SEO, $SEO3);
	dataMerge($SEO, $SEO2);
	dataMerge($SEO, $SEO1);
	$SEO[':replace']	= $SEO_data;

	return $SEO;
}
?>
