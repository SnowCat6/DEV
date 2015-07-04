<?
function doc_page_url(&$db, $val, &$data)
{
	//	Обработка перехода по ссылке
	$id			= (int)$data[1];
	$db->sql	= "(`visible` = 1 OR `doc_type` = 'product')";
	$data		= $db->openID($id);
	if (!$data)	return docPage404();

	m("clipboard:add:doc_visit", $id);
	return docPageEx($db, $id, $data, true);	
}
function doc_page(&$db, $val, $search)
{
	if (!is_array($search)) $search = array();
	if ($val)	$search['id']	= $val;

	if ($search['id']){
		$id		= alias2doc($search['id']);
		if (defined("docPage$id")) return;
		define("docPage$id", true);
		
		$data	= $db->openID($id);
		if ($data) return docPageEx($db, $val, $data, false);
		return docPage404();
	}
	
	$sql	= doc2sql($search);
	if (!$sql) return;
	
	$db->open($sql);
	while($data = $db->next()){
		docPageEx($db, $val, $data, false);
	}
}
//	Вернуть правила отображения страницы
function doc_pageRule($db, $template, $data)
{
	$rules	= getIniValue(':docRules');
	list(, , $rule, $pageTemplate)	= explode(':', $rules["$data[doc_type]:$data[template]"]);
	$baseTemplate	= explode('.', $data['template'], 2);
	$baseTemplate	= $baseTemplate[0];

	$page	= $data['fields']['page'];
	if (!$page) $page = $pageTemplate;

	$fn	= getFn(array(
		$rule,
		'doc_page_' . $template,
		'doc_page_' . $template . '_' . $baseTemplate,
		'doc_page_' . $data['doc_type']. '_' . $baseTemplate,
		'doc_page_' . $data['doc_type'],
		'doc_page_default_' . $baseTemplate,
		'doc_page_default'
	));
	
	return array(
		'fn'	=> $fn,
		'page'	=> $page,
		'class'		=> "$data[doc_type]:$data[template]",
		'baseClass'	=> "$data[doc_type]:$baseTemplate"
	);
}

function docPageEx(&$db, $val, &$data, $bThisPage)
{
	$id		= $db->id();
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
	
	$rule	= doc_pageRule($db, $template, $data);
	
	if ($bThisPage)
	{
		currentPage($id);
		moduleEx('page:title', $data['title']);
		module('SEO:set', module("doc:SEOget:$id", $data));
		
		if ($rule['page'] && !testValue('ajax')) setTemplate($rule['page']);
	}
	
	event('document.begin',	$id);
	$rule['fn']($db, $menu, $data);
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
//	+function doc_SEOget
function doc_SEOget($db, $id, $data)
{
	$SEO_data			= getCache("SEO_data", "doc$id");
	if (!is_array($SEO_data))
	{
		$SEO_data['title']		= $data['title'];
		$SEO_data['description']= docNote($data);
		$SEO_data['annotation']	= $data['fields']['note'];
//		$SEO_data['publish']	= module('date:%d %F %Y', $data['datePublish']);

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
