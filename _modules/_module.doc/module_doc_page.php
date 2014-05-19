<?
function doc_page(&$db, $val, &$data)
{
	$id	=0;
	if ($val != 'url'){
		//	Обработка ручного вывода
		list($id, $template) = explode(':', $val);
		$id	= alias2doc($id);
	}else{
		//	Обработка перехода по ссылке
		$id	= (int)$data[1];
	}
	
	$cacheName	= NULL;
	if (!memBegin($cacheName)) return;

	$noCache	= getNoCache();
	$db->sql	= "(`visible` = 1 OR `doc_type` = 'product')";
	$data		= $db->openID($id);

	if (!$data){
		memEndCancel();
		return event('site.noPageFound', $val);
	}

	$idBase	= $id;
	$fields	= $data['fields'];
	$menu	= doc_menu($id, $data, false);

	$redirect	= $fields['redirect'];
	if ($redirect){
		$id 	= alias2doc($redirect);
		$data	= $db->openID($id);
		if (!$data){
			memEndCancel();
			return event('site.noPageFound', $val);
		}
		$menu	= doc_menu($id, $data, false);
		if (access('write', "doc:$idBase")) $menu['Изменить оригинал#ajax'] = getURL("page_edit_$idBase");
	}
	
	if ($val == 'url')
	{
		currentPage($id);
		moduleEx('page:title', $data['title']);
		
		$page	= $fields['page'];
		if ($page && !testValue('ajax')) setTemplate($page);

		$note	= $fields['note'];
		if ($note){
			moduleEx("page:meta:description", $note);
		}

		
		$SEO	= $fields['SEO'];
		$title	= $SEO['title'];
		if ($title){
			moduleEx('page:title:siteTitle', $title);
		}

		if (is_array($SEO)){
			foreach($SEO as $name => $val){
				if ($name == 'title') continue;
				if ($val){
					moduleEx("page:meta:$name", $val);
				}
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

	if ($fn)	$fn($db, $menu, $data);
	event('document.end',	$id);

	if ($noCache == getNoCache())	memEnd();
	else memEndCancel();
}
?>
