<? // Module module_admin loaded from  _modules/_module.admin/module_admin.php ?>
<?
function module_admin(&$fn, &$data)
{
	if (!defined('userID')) return;
//	if (!access('write', '')) return;

//	noCache();
//	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, $data):NULL;
}

function module_access($access, $data){
	return hasAccessRole($access);
}

function beginAdmin(){
	ob_start();
}

function endAdmin($menu, $bTop = true){
	$content = ob_get_clean();
	if (!$menu) return print($content);
	$menu[':useTopMenu']= $bTop;
	$menu[':layout'] 	= $content;
	module('admin:edit', $menu);
}

function startDrop($search, $template = '', $bSortable = false){
	if (!$search || testValue('ajax')) return;
	$rel = makeQueryString($search, 'data');
	$class= $bSortable?' class="sortable"':'';
	echo "<div rel=\"droppable:$rel&template=$template\"$class>";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	echo "</div>";
}
function module_admin_cache($val, $data)
{
	if (!access('clearCache', '')) return;

	if (testValue('clearCode'))
	{
		clearCacheCode();
		module('message', 'Кеш кода очищен.');
	}else
	if (testValue('clearCache'))
	{
		clearCache();
		module('doc:clear');
		module('message', 'Кеш очищен, перезагрузите страницу.');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
	}else
	if (testValue('clearThumb')){
		clearThumb(images);
		clearCache();
		module('doc:clear');
		module('message', 'Миниизображения удалены');
	}
}
?>
<? // Module module_script_ajax loaded from  _modules/_module.ajax/module_script_ajax.php ?>
<?
//	Обработчик страницы, если передано значение ajax, то меняет стандартный шаблон выводимого документа на AJAX шаблон
function module_script_ajax($val, &$config)
{
	if (!testValue('ajax')) return;

	$ajaxTemplate = getValue('ajax');
	$config['page']['template'] = $ajaxTemplate?"page.$ajaxTemplate":'page.ajax';
}?>

<? // Module module_doc loaded from  _modules/_module.doc/module_doc.php ?>
<?
function module_doc($fn, &$data)
{
	$sql		= array();
	$sql[]		= '`deleted` = 0';

	//	Если есть опция показывать скрытые, то она доступна только элите, для всех остальных игнорируется
	if (getValue('showHidden') && hasAccessRole('admin,developer,writer,manager') ){
	}else{
		$sql[]		= "`visible` = 1";
//		$sql[]		= "`visible` = 1 AND (`doc_type` <> 'product' OR `price` > 0)";
	}
	//	База данных пользователей
	$db 		= new dbRow('documents_tbl', 'doc_id');
	$db->sql	= implode(' AND ', $sql);
	$db->images = images.'/doc';
	$db->url 	= 'page';
	$db->setCache();
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("doc_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function doc_name($db, $id, $option){
	$data = $db->openID(alias2doc($id));
	if (!$data) return;

	$name = htmlspecialchars($data['title']);
	if ($option == 'link'){
		$url = getURL($db->url($id));
		echo "<a href=\"$url\">$name</a>";
	}else echo $name;
}
function doc_path($db, $id, $data)
{
	if (!$id) $id = currentPage();
	
	$split	= '';
	$path	= getPageParents($id);
	foreach($path as $iid){
		echo $split;
		doc_name($db, $iid, "link");
		$split = ' / ';
	}
}
function docNote(&$data, $nLen = 200){
	return makeNote($data['originalDocument'], $nLen);
}
function currentPage($id = NULL){
	if ($id != NULL) $GLOBALS['_SETTINGS']['page']['currentPage'] = $id;
	else return @$GLOBALS['_SETTINGS']['page']['currentPage'];
}
function docDraggableID($id, &$data){
	if (!access('write', "doc:$id")) return;
	module('script:draggable');
	return "rel=\"draggable-doc-ajax_edit_$id-$data[doc_type]\"";
}
function docURL($id){
	$db = module('doc');
	return $db->url($id);
}
function currentPageRoot($index = 0)
{
	$thisID = currentPage();
	if (!$thisID) return;
	$parents= getPageParents($thisID);
	@$parent= $parents[$index];
	return @$parent?$parent:$thisID;
}
function getPageParents($id){
	$parents	= array();
	$prop		= module("prop:get:$id");
	while(@$parent= (int)$prop[':parent']['property']){
		if (is_int(array_search($parent, $parents))) break;
		$parents[] 	= $parent;
		$id			= $parent;
		$prop		= module("prop:get:$id");
	}
	return array_reverse($parents);
}

function alias2doc($val)
{
	if (is_array($val)) return makeIDS($val);
	if ($val == 'root')	return currentPageRoot();
	if ($val == 'this')	return currentPage();

	if (preg_match('#^(\d+)$#', $val))
		return (int)$val;
	if (preg_match('#/page(\d+)\.htm#', $val, $v))
		return (int)$v[1];

	$nativeURL	= module("links:getLinkBase", $val);
	if (!$nativeURL){
		$v			= "/$val.htm";
		$nativeURL	= module("links:getLinkBase", $v);
	}
	if ($nativeURL && preg_match('#/page(\d+)#', $nativeURL, $v))
		return (int)$v[1];
}
function docType($type, $n = 0)
{
	$docTypes	= getCacheValue('docTypes');
	$names		= explode(':',  $docTypes[$type]);
	return @$names[$n];
}
function docTitleImage($id){
	$db		= module('doc');
	$folder	= $db->folder($id);
	@list($name, $path) = each(getFiles("$folder/Title"));
	return $path;
}
function doc_clear($db, $id, $data){
	$a = array();
	setCacheValue('textBlocks', $a);
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `document` = NULL");
	
	m('prop:clear');
}
function doc_recompile($db, $id, $data)
{
	$a = array();
	setCacheValue('textBlocks', $a);
	
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->setValue($ids, 'document', NULL, false);
	}else{
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
		
		$ddb	= module('doc');
		$db->open("`searchDocument` IS NULL");
		while($data = $db->next()){
			$d	= array();
			$d['searchTitle']	= docPrepareSearch($data['title']);
			$d['searchDocument']= docPrepareSearch($data['originalDocument']);
			$ddb->setValues($db->id(), $d);
			$db->clearCache();
		}
		
	}
}
function showDocument($val, $data = NULL)
{
	//	{\{moduleName=values}\}
	//	Специальная версия для статических страниц
	$val= preg_replace_callback('#{{([^}]+)}}#u', parsePageModuleFn, $val);
	echo $val;
}
function parsePageModuleFn($matches)
{
	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$baseCode	= $matches[1];
	@list($moduleName, $moduleData) = explode('=', $baseCode, 2);
	//	name:val;nam2:val
	$module_data= array();
	$d			= explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if ($val){
			$d2		= &$module_data;
			$name	= explode('.', $name);
			foreach($name as $n) @$d2 = &$d2[$n];
			$d2	= $val;
		}else{
			$module_data[] = $name;
		}
	}
	
	return m($moduleName, $module_data);
}

function doc_childs($db, $deep, &$search)
{
	$tree	= array();
	$childs	= array();
	$deep	= (int)$deep;
	if ($deep < 1) return array();

	if (@!$search['type']) $search['type'] = 'page,catalog';

	for($ix = 0; $ix < $deep; ++$ix)
	{
		$ids	= array();
		$db->open(doc2sql($search));
		while($db->next()){
			$id		= $db->id();
			$ids[]	= $id;
			$prop	= module("prop:get:$id");

			$parents= explode(', ', $prop[':parent']['property']);
			foreach($parents as $parent){
				$parent = (int)$parent;
				$childs[$parent][$id] = array();
				if ($ix == 0) $tree[$parent] = array();
			}
		}
		$search	= array('parent'=>$ids, 'type'=>$search['type']);
	}

	foreach($tree as $parent => &$c)
	{
		$c		= $childs[$parent];
		if (!is_array($c)) $c = '';
		
		$stop	= array();
		docMaketree($tree, $childs, $stop);
	}
	$tree[':childs'] = $childs;
	
	return $tree;
}

function docMakeTree(&$tree, &$childs, &$stop)
{
	foreach($tree as $parent => &$c)
	{
		if (isset($stop[$parent])) continue;
		$stop[$parent] = true;
		
		$c = $childs[$parent];
		if (!is_array($c)) $c = '';
		else docMakeTree($c, $childs, $stop);
	}
}
?>
<? // Module module_doc_access loaded from  _modules/_module.doc/module_doc_access.php ?>
<?
function module_doc_access($mode, $data)
{
	@$id	= (int)$data[1];
	switch($mode){
		case 'read': 
			return true;
		case 'add':
			return module_doc_add_access($mode, $data);
		case 'write':
			return hasAccessRole('admin,developer,writer,manager,SEO');
		case 'delete':
			return hasAccessRole('admin,developer,writer');
	}
}

function module_doc_add_access($mode, $data)
{
	if ($mode != 'add') return false;

	@$baseType	= $data[1];
	if ((int)$baseType){
		$db = module('doc');
		$d	= $db->openID($baseType);
		@$baseType = $d['doc_type'];
	}
	@$newType	= $data[2];
	switch("$baseType:$newType")
	{
		case 'page:':
		case 'page:page':
		case 'page:article':
//		case 'page:catalog':
		case 'catalog:catalog';
		case 'catalog:';
			return hasAccessRole('admin,developer,writer');

		case 'article:';
		case 'product:';
		case 'catalog:product';
			return hasAccessRole('admin,developer,writer,manager');

		case 'article:comment':
			return hasAccessRole('admin,developer,writer,manager,user');
		case 'product:comment';
			return true;
	}
	return false;
}

?>
<? // Module module_doc_cache loaded from  _modules/_module.doc/module_doc_cache.php ?>
<? function doc_cacheSet($db, $id, $cacheData)
{
	@list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;

	$d						= array();
	$d['document']			= $data['document'];
	$d['document'][$name]	= $cacheData;
	$GLOBALS['_CONFIG']['docCache'][$id] = $d;
	
	$data['document'] = $d['document'];
	$db->setCacheData($id, $data);
	module('message:trace', "Document cache set, $id => $name");
}
function doc_cacheFlush($db, $val, $data)
{
	$cache		= &$GLOBALS['_CONFIG']['docCache'];
	if (!$cache) return;
	
	foreach($cache as $id => &$d){
		$d['id']	= $id;
		$iid		= $db->update($d);
	}
}
function doc_cacheGet($db, $id, $data)
{
	@list($id, $name) = explode(':', $id, 2);
	if (!$name) retrun;
	
	$data = $db->openID($id);
	if (!$data) return;
	
	return @$data['document'][$name];
}
function getDocument(&$data){
	ob_start();
	document($data);
	return ob_get_clean();
}
function document(&$data){
	if (!beginCompile($data, 'document')) return;
	echo $data['originalDocument'];
	endCompile($data, 'document');
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	$id		= $data['doc_id'];
	$cache	= module("doc:cacheGet:$id:$renderName");
	if (isset($cache)){
		showDocument($cache, $data);
		return false;
	}
	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName)
{
	$id			= $data['doc_id'];
	$document	= ob_get_clean();
	event('document.compile', $document);
	showDocument($document, $data);
	if (!localCacheExists()) return;
	module("doc:cacheSet:$id:$renderName", $document);
}
?>
<? // Module module_doc_page loaded from  _modules/_module.doc/module_doc_page.php ?>
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
		$ddb	= $db;
		$id		= $ddb->id();
		$idBase	= $id;
		@$fields= $data['fields'];
		$menu		= doc_menu($id, $data, false);
		
		@$redirect	= $fields['redirect'];
		if ($redirect){
			$id 	= alias2doc($redirect);
			$ddb	= module('doc');
			$data	= $ddb->openID($id);
			if (access('write', "doc:$idBase")) $menu['Изменить оригинал#ajax'] = getURL("page_edit_$idBase");
		}
		
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
		if ($fn)	$fn($ddb, $menu, $data);
		event('document.end',	$id);
	}
}
?>

<? // Module module_doc_read loaded from  _modules/_module.doc/module_doc_read.php ?>
<?
function doc_read(&$db, $template, &$search)
{
	list($template, $val)  = explode(':', $template, 2);
	$fn = getFn("doc_read_$template");

	if (!$fn) $fn = getFn('doc_read_default');

	$fn2 = getFn("doc_read_$template"."_before");
	if (!$fn2)$fn2 = getFn('doc_read_default_before');
	if ($fn2) $fn2($db, $val, $search);
	
	$order		= @$search[':order'];
	if (!$order) $order = '`sort`, `datePublish` DESC';
	$db->order	= $order;
	
	@$max		= (int)$search[':max'];
	if ($max > 0) $db->max = $max;

	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;
//define('_debug_', true);
	$db->open($sql);
	
	ob_start();
	$search = $fn?$fn($db, $val, $search):NULL;
	$p		= ob_get_clean();
	
	if (is_array($search) && access('write', 'doc:0')){
		startDrop($search, $template);
		echo $p;
		endDrop($search, $template);
	}else{
		echo $p;
	}
	$fn2 = getFn("doc_read_$template"."_after");
	if (!$fn2)$fn2 = getFn('doc_read_default_after');
	if ($fn2) $fn2($db, $val, $search);

	return $db->rows();
}
?>

<? // Module module_doc_sql loaded from  _modules/_module.doc/module_doc_sql.php ?>
<?
function doc2sql($search){
	$sql = array();
	doc_sql($sql, $search);
	return $sql;
}
function doc_sql(&$sql, &$search)
{
	$path	= array();
/*
	if (@$search['parent'] == 'this') $search['parent'] = currentPage();
	if (@$search['parent'] == 'root') $search['parent'] = currentPageRoot();
	if (@$search['parent*']== 'this')$search['parent*']	= currentPage();
	if (@$search['parent*']== 'root')$search['parent*']	= currentPageRoot();
*/	///////////////////////////////////////////
	//	Найти по номеру документа
	if (isset($search['id']))
	{
		$val	= $search['id'];
		if (is_string($val)) $val	= alias2doc($val);
		$val	= makeIDS($val);
		
		if ($val) $sql[]	= "`doc_id` IN ($val)";
		else $sql[] = 'false';
	}

	if (@$val = $search['title'])
	{
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`title` LIKE ('%$val%')";
	}

	if (@$val = $search['template'])
	{
		makeSQLValue($val);
		$sql[]	= "`template` = $val";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`doc_type` IN ($val)";
	}
	
	//	Если ищется по имени
	if ($val = @$search['name']){
		$s = array();

		//	Или название / рус, енг
		$v = docPrepareSearch($val);
		$v = trim($v);
		if ($v){
			$e		= array();	//	Exclude words
//			if (is_int(strpos('вентилятор', $v))) $e[] = 'обогреватель';
			
			$name 	= htmlspecialchars(docPrepareSearch($val, false));
			$path[] = "название <b>$name</b>";
			$v 		= str_replace(' ', '* +', $v);
			
			if ($e)	$e = ' -'.implode(' -', $e);
			else $e = '';
			
			$s[]	= "MATCH (`searchTitle`) AGAINST ('+$v*$e' IN BOOLEAN MODE)";
		}
		if ($s)	$sql[] = '('.implode(' OR ', $s).')';
	}
	//	Если ищется по имени
	if ($val = @$search['document']){
		$s = array();

		//	Или название / рус, енг
		$v = docPrepareSearch($val);
		$v = trim($v);
		if ($v){
			$e		= array();	//	Exclude words
//			if (is_int(strpos('вентилятор', $v))) $e[] = 'обогреватель';
			
			$name 	= htmlspecialchars(docPrepareSearch($val, false));
			$path[] = "слова <b>$name</b>";
			$v 		= str_replace(' ', '* +', $v);
			
			if ($e)	$e = ' -'.implode(' -', $e);
			else $e = '';
			
			$s[]	= "MATCH (`searchTitle`, `searchDocument`) AGAINST ('+$v*$e' IN BOOLEAN MODE)";
		}
		if ($s)	$sql[] = '('.implode(' OR ', $s).')';
	}

	$ev = array(&$sql, &$search);
	event('doc.sql', $ev);
	
	if (@$sql[':from'] || @$sql[':join']){
		$sql[':from'][] = 'd';
	}

	return $path;
}
//	Убрать все неиндексируемые символы, одиночные буквы и цифры расщирить до 4х знаков
function docPrepareSearch($val, $bFullPrepare = true)
{
	$val = strip_tags($val);
	$val = preg_replace('#&(\w+);#', ' ', $val);
	$val = preg_replace('#[^a-zа-я\d]#iu', ' ', $val);
	$val = preg_replace('#\s+#u', ' ', $val);

	if (!$bFullPrepare) return $val;

	$val = preg_replace('#\b(\w{1})\b#u', '\\1xyz',	$val);
	$val = preg_replace('#\b(\w{2})\b#u', '\\1yz',	$val);
	$val = preg_replace('#\b(\w{3})\b#u', '\\1z',	$val);
	
	//	65kb maximum TEXT field length
	//	FULLTEXT index possible only with TEXT fueld
	$val = substr($val, 0, 65000);
	
	return $val;
}

?>
<? // Module module_doc_menu loaded from  _modules/_module.doc/_edit/module_doc_menu.php ?>
<?
function doc_menu($id, &$data, $bSimple = true)
{
	$menu		= array();
	$bHiddable	= false;

	if (!$bSimple && access('add', "doc:$id:article")){
		$docType	= docType('article');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=article');
	}

	if (!$bSimple && access('add', "doc:$id:page")){
		$docType	= docType('page');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=page');
	}

	if (!$bSimple && access('add', "doc:$id:product")){
		$docType	= docType('product');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=product');
	}

	if (!$bSimple && access('add', "doc:$id:catalog")){
		$docType	= docType('catalog');
		$menu["Добавть $docType#ajax_edit"]	= getURL("page_add_$id", 'type=catalog');
	}

	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");
		$menu[':draggable']			= docDraggableID($id, $data);
	}

	if (!$bSimple && access('delete', "doc:$id"))
		$menu['Удалить#ajax_dialog']	= getURL("page_edit_$id", 'delete');
		
	return $menu;
}

function doc_admin($db, $val, $data)
{
	@list($action, $id, $type) = explode(':', $val);
	$id		= alias2doc($id);

	switch($action){
	case 'add':
		$data	= $db->openID($id);
		if (!access('add', "doc:$data[doc_type]:$type")) return;
		$url	= getURL("page_add_$id", "type=$type");
		echo " <a href=\"$url\" id=\"ajax_edit\">+</a>";
		break;
	}
}
?>
<? // Module module_price loaded from  _modules/_module.doc/_module.price/module_price.php ?>
<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn($val, $data):NULL;
}
function docPrice(&$data, $name = ''){
	if ($data['doc_type'] != 'product') return;
	if ($name == '') $name = 'base';
	switch($name){
	case 'old':		@$price	= $data['price_old'];	break;
	case 'base':	@$price	= $data['price'];		break;
	}
	return (float)$price;
}
function priceNumber($price){
	$price = str_replace(' ', '', $price);
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = ''){
	$price = docPrice($data, $name);
	if (!$price) return;
	
	$price = priceNumber($price);
	if ($name == 'old') return "<span class=\"price old\">$price</span>";
	return "<span class=\"price\">$price</span>";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat($data, $name);
	if ($price) return "<span class=\"priceName\">Цена: $price руб.</span>";
}
function price_update($val, &$evData)
{
	$d		= &$evData[0];
	$data	= &$evData[1];
	
	if (isset($data['price']))
	{
		$price = (float)$data['price'];
		$d['price']		= $price;
		$price = (float)$data['price_old'];
		$d['price_old']	= $price;
	}
}
function price_query($val, &$evData)
{
	foreach(explode("\r\n", $evData[0]) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL(trim($q));
		if ($name && $q) $evData[1][$name]	= $q;
	};
}
function makePropertySQL($q)
{
	list($q1, $q2) = explode('-', $q);
	$q1 = (int)$q1;
	$q2 = (int)$q2;
	
	if ($q1 && $q2){
		return "(`price` >= $q1 AND `price` < $q2)";
	}else
	if ($q1){
		return "`price` >= $q1";
	}else
	if ($q2){
		return "`price` <= $q2";
	}
}

?>
<? // Module module_price_sql loaded from  _modules/_module.doc/_module.price/module_price_sql.php ?>
<?
function module_price_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	///////////////////////////////////////////
	//	Найти по цене
	if (@isset($search['price']))
	{
		$val		= $search['price'];
		$where		= '';
		$val		= explode('-', $val);
		@list($priceFrom, $priceTo) = $val;
		$priceFrom	= (float)trim($priceFrom);
		$priceTo	= (float)trim($priceTo);
		
		if ($priceFrom && $priceTo){
			$sql[] = "`price` BETWEEN $priceFrom AND $priceTo";
		}else
		if ($priceTo){
			$sql[] = "`price` <= priceTo";
		}else
		if (count($val) > 1) $sql[] = "price >= $priceFrom";
		else  $sql[] = "`price` = $priceFrom";
	}
}
?>
<? // Module module_prop loaded from  _modules/_module.doc/_module.property/module_prop.php ?>
<?
function module_prop($fn, &$data)
{
	//	База данных пользователей
	$db	= new dbRow('prop_name_tbl', 'prop_id');
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');
	$db->dbValues= new dbRow('prop_values_tbl','values_id');

	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("prop_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function propFormat($val, &$data, $bUseFormat = true){
	if ($format = $data['format'])
		return $bUseFormat?str_replace('%', "</span>$val<span>", "<span class=\"propFormat\"><span>$format</span></span>"):str_replace('%', $val, $format);
	return $bUseFormat?"<span class=\"propFormat\">$val</span>":$val;
}

function prop_get($db, $val, $data)
{
	@list($docID, $group)  = explode(':', $val, 2);
	
	$bNoCache	= false;
	
	$docID	= makeIDS($docID);
	$ids	= explode(',', $docID);
	if (count($ids) != 1) $bNoCache = true;
	
	if ($group)	$group	= explode(',', $group);
	else $group = array();
	
	if (!$bNoCache)
	{
		$ddb	= module('doc');
		$data	= $ddb->openID($docID);
		if (!$data) return array();

		@$res	= unserialize($data['property']);
		if (is_array($res))
		{
			if (!$group) return $res;
			foreach($res as $name => &$data){
				$g = explode(',', $data['group']);
				if (!array_intersect($group, $g)) unset($res[$name]);
			}
			return $res;
		}
	}
	
	$res	= array();
	$sql	= array();
	$sql[]	= "v.`doc_id` IN ($docID)";
	$sql[':from']['prop_name_tbl']	= 'p';
	$sql[':from']['prop_value_tbl']	= 'v';
	$table2	= $db->dbValues->table();
	$sql[':join']["$table2 AS vs"]	= 'vs.`values_id` = v.`values_id`';
	$sql[]		= "p.`prop_id` = v.`prop_id`";
	$db->group	= 'p.`prop_id`';
	$db->order	= 'p.`sort`';

	$unuinSQL	= array();
	$sql['type']= "p.`valueType` = 'valueDigit'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueDigit` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);
	
	$sql['type']= "p.`valueType` = 'valueText'";
	$db->fields	= "p.*, GROUP_CONCAT(DISTINCT vs.`valueText` SEPARATOR ', ') AS `property`";
	$unuinSQL[]	= $db->makeSQL($sql);

	$union		= '(' . implode(') UNION (', $unuinSQL) .') ORDER BY `sort`';
	$db->exec($union);

	while($data = $db->next())
	{
		if ($bNoCache){
			$g = explode(',', $data['group']);
			if (!array_intersect($group, $g)) continue;
		}
		$res[$data['name']] = $data;
	}
	
	if ($bNoCache) return $res;

	$ddb->setValue($docID, 'property', $res, false);
	if (!$group) return $res;
	
	foreach($res as $name => &$data){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) unset($res[$name]);
	}
	
	return $res;
}
function prop_set($db, $docID, $data)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$docIDS	= $docID;
		$docID	= explode(',', $docID);
	}

	if (!is_array($data)) return;
	
	$a	= array();
	setCacheValue('propNames', $a);

	$ids	= array();
	$ddb	= module('doc');
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= moduleEx("prop:add:$name", $valueType);
		if (!$iid || !$docID) continue;

		$props	= array();
		$propsID= array();
		//	Все свойства документов
		$sql	= array();
		$sql[]	= "`prop_id` = $iid AND `doc_id` IN ($docIDS)";
		$db->dbValue->open($sql);
		while($d = $db->dbValue->next()){
			//	Создать массиво имеющихся свойств
			//	doc_id:value => id
			$key	= "$d[doc_id]:$d[values_id]";
			$ixd	= $db->dbValue->id();
			$props[$key]	= $ixd;
			$propsID[$ixd]	= $ixd;
		}
		//	Проверить каждое значение свойства
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			$val = trim($val);
			if (!$val){
				$db->dbValue->delete("doc_id IN ($docID) AND prop_id = `$iid`");
				$ddb->setValue($docID, 'property', NULL);
				continue;
			}
			
			if ($valueType == 'valueDigit'){
				$v = (int)$val;
			}else{
				$v = $val; makeSQLValue($v);
			}
			$db->dbValues->open("`$valueType` = $v");
			$d = $db->dbValues->next();
			if (!$d){
				$d = array();
				$d['valueDigit']= (int)$val;
				$d['valueText']	= $val;
				$valuesID = $db->dbValues->update($d, false);
			}else{
				$valuesID = $db->dbValues->id();
			}

			foreach($docID as $doc_id)
			{
				//	Если такое значение уже есть, не добавлять
				$key = "$doc_id:$valuesID";
				if (@$ixd = $props[$key]){
					unset($propsID[$ixd]);
				}else{
					$d				= array();
					$d['prop_id']	= $iid;
					$d['doc_id'] 	= $doc_id;
					$d['values_id']	= $valuesID;
					$ixd = $db->dbValue->update($d, false);
					$props[$key]	= $ixd;
				}
				$ids[$doc_id] = $doc_id;
			}
		}
		if ($ids){
			$ddb->setValue($ids, 'property', NULL);
		}
		if ($propsID)	$db->dbValue->delete($propsID);
	}
}

function prop_delete($db, $docID, $data)
{
	$db->dbValue->deleteByKey('doc_id', $docID);
	
	$ddb = module('doc');
	$ddb->setValue($docID, 'property', NULL);
}

function prop_add($db, $name, &$valueType)
{
	$name		= trim($name);
	@$aliases	= &$GLOBALS['_CONFIG']['propertyAliases'];
	if (!is_array($aliases)){
		$aliases = array();
		$db->open();
		while($data = $db->next()){
			$alias = explode("\r\n", $data['alias']);
			foreach($alias as $key) $aliases[strtolower($key)] = $data['name'];
		}
	}
	@$alias = trim($aliases[strtolower($name)]);
	if ($alias) $name = $alias;
	
	if (!$valueType) $valueType = 'valueText';
	$n		= $name; makeSQLValue($n);

	$db->open("name = $n");
	if ($data = $db->next()){
		$iid		= $db->id();
		$valueType	= $data['valueType'];
	}else{
		$d			= array();
		$d['name']	= $name;
		$d['valueType'] = $valueType;
		$d['group']	= $group;
		$iid		= $db->update($d, false);
	}
	
	return $iid;
}

function prop_filer(&$prop)
{
	foreach($prop as $name => &$val)
	{
		if ($name[0] != ':') continue;
		if (hasAccessRole('developer')) continue;
		unset($prop[$name]);
	}
}
function prop_value($db, $names, $dtaa)
{
	$ret	= array();
	$names	= explode(',', $names);
	foreach($names as &$name){
		makeSQLValue($name);
	}
	
	$names = implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		$valueType	= $data['valueType'];
		$values		= explode("\r\n", $data['values']);
		foreach($values as $n){
			$n = trim($n);
			if ($n) $ret[$name][$n] = $n;
		}
		
		$db->dbValue->fields= $valueType;
		$db->dbValue->group	= $valueType;
		$db->dbValue->order	= $valueType;
		$db->dbValue->open("`prop_id` = $id");
		while($d = $db->dbValue->next())
		{
			$n = $d[$valueType];
			if ($n) $ret[$name][$n] = $n;
		}
	}
	return $ret;
}
function prop_count($db, $names, &$search)
{
	$ddb	= module('doc');
//////////////
	$key	= $ddb->key();
	$table	= $ddb->table();
	$search['price']	= '1-';
	$sql	= doc2sql($search);
	$ids	= $ddb->selectKeys($key, $sql);
	if (!$ids) return array();
	$ddb->sql	=	'';
///////////////
	$ret	= array();
	$union	= array();

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();

	$names	= explode(',', $names);
	foreach($names as &$name) makeSQLValue($name);
	$names	= implode(',', $names);
	$db->open("`name` IN ($names)");
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= $data['name'];
		makeSQLValue($name);
		$sort	= $data['sort'];
		$sort2	= 0;

		$queryName	= $data['queryName'];
		$ev			= array(&$data['query'], array());
		if ($queryName) event("prop.query:$queryName", $ev);

		if ($query = &$ev[1])
		{
			$sql	= array();
			$fields	= "''";
			$fields2= $sort;
			foreach($query as $n => $q){
				makeSQLValue($n);
				$fields = "IF($q, $n, $fields)";
				$fields2= "IF($q, $sort2, $fields2)";
				++$sort2;
			}
			$ddb->fields= "$name AS name, $fields AS value, $sort AS sort, $fields2 AS sort2, count(*) AS cnt";
			$ddb->group	= 'value';
			$sql[]		= "`$key` IN ($ids)";
			$union[]	= $ddb->makeSQL($sql);
		}else{
			$sql	= array();
			$sql[':join']["$table2 AS pv$id"]	= "p$id.`values_id` = pv$id.`values_id`";
			$db->dbValue->group		= "pv$id.`values_id`";
			$sql[':where']	= "p$id.`prop_id`=$id";

			$sql[]			= "`$key` IN ($ids)";
			$sql[':from'][]	= "p$id";
			
			$db->dbValue->fields	= "$name AS name, pv$id.`$data[valueType]` AS value, $sort AS sort, $sort2 AS sort2, count(*) AS cnt";
			$union[]	= $db->dbValue->makeSQL($sql);
		}
	}
	$union	= '(' . implode(') UNION (', $union) . ') ORDER BY `sort`, `sort2`';
	$ddb->exec($union);
	while($data = $ddb->next()){
		$count	= $data['cnt'];
		if ($count) $ret[$data['name']][$data['value']] = $count;
	}
	
	return $ret;
	
	$ddb		= module('doc');
	$sql		= array();
	$unionSQL	= array();
	
	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql[':join']["$table AS p"]	= 'p.`doc_id` = `doc_id`';
	$sql[':join']["$table2 AS vs"]	= 'vs.`values_id` = p.`values_id`';
	
	$table	= $db->table();
	if ($names){
		$names	= explode(',', $names);
		foreach($names as &$name) makeSQLValue($name);
		$names	= implode(',', $names);
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueText'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueText'";
	}
	$sql2	= $sql;
	$sql2[':join']["$table AS pn"] = 'pn.`prop_id` = p.`prop_id`';
	$sql2[]	= $thisSQL;
	doc_sql($sql2, $search);
	
	$ddb->fields= 'pn.`name`, pn.`sort`, vs.`valueText` AS val, count(*) AS cnt';
	$ddb->group	= 'val';
	$unuinSQL[]	= $ddb->makeSQL($sql2);
	
	if ($names){
		$thisSQL= "pn.`name` IN ($names) AND pn.`valueType` = 'valueDigit'";
	}else{
		$thisSQL= "pn.`valueType` = 'valueDigit'";
	}
	$sql2	= $sql;
	$sql2[':join']["$table AS pn"] = 'pn.`prop_id` = p.`prop_id`';
	$sql2[]		= $thisSQL;
	doc_sql($sql2, $search);

	$ddb->fields= 'pn.`name`, pn.`sort`, vs.`valueDigit` AS val, count(*) AS cnt';
	$ddb->group	= 'val';
	$unuinSQL[]	= $ddb->makeSQL($sql2);

	$union		= '(' . implode(') UNION (', $unuinSQL) . ') ORDER BY `sort`, `name`, `val`';
	
	$ret	= array();
	$ddb->exec($union);
	while($data = $ddb->next()){
		$ret[$data['name']][$data['val']] = $data['cnt'];
	}

	return $ret;
}
function prop_name($db, $group, $data)
{
	$db->order	= '`name`';
	$group	= explode(',', $group);
	$ret	= array();
	$db->open();
	while($data = $db->next()){
		$g = explode(',', $data['group']);
		if (!array_intersect($group, $g)) continue;
		$ret[$data['name']] = $data;
	}
	return $ret;
}
function prop_clear($db, $id, $data)
{
	if ($id){
		$ids		= makeIDS($id);
		$ddb		= module('doc');
		$table		= $db->dbValue->table();
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable AS d INNER JOIN $table AS p ON d.`doc_id` = p.`doc_id` SET `property` = NULL  WHERE p.`prop_id` IN ($ids)";
		$ddb->exec($sql);
	}else{
		$ddb		= module('doc');
		$docTable	= $ddb->table();
		$sql		= "UPDATE $docTable SET `property` = NULL";
//		$ddb->exec($sql);
	}

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$sql	= "DELETE vs FROM $table2 AS vs WHERE `values_id` NOT IN (SELECT `values_id` FROM $table)";
	$db->exec($sql);
	
	$dbDoc		= module('doc');
	$docTable	= $dbDoc->table();
	$sql		= "DELETE v FROM $table AS v WHERE `doc_id` NOT IN (SELECT doc_id FROM $docTable)";
	$db->exec($sql);
}
function prop_addQuery($db, $query, $queryName)
{
	$q	= getCacheValue('propertyQuery');
	if (!is_array($q)) $q = array();
	$q[$query] = $queryName;
	setCacheValue('propertyQuery', $q);
}
?>
<? // Module module_prop_read loaded from  _modules/_module.doc/_module.property/module_prop_read.php ?>
<?
function prop_read($db, $fn, $data)
{
	$props = module("prop:get:$data[id]:$data[group]");
	if (!$props) return;
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("prop_read_$fn");
	if ($fn) return $fn($props, $val);

	$split = '<ul>';
	foreach($props as $name => $data)
	{
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		echo $split; $split = '';
		$note	= htmlspecialchars($data['note']);
		$name	= htmlspecialchars($name);
		$prop	= htmlspecialchars($data['property']);
		
		if ($prop){
			$prop	= propFormat($prop, $data, true);
			echo "<li title=\"$note\">$name: <b>$prop</b></li>";
		}else{
			echo "<li title=\"$note\">$name</li>";
		}
	}
	if (!$split) echo '</ul>';
}

function prop_read_plain(&$props)
{
	$split = '';
	foreach($props as $name => $data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		$prop	= htmlspecialchars($data['property']);
		if (!$prop) continue;
		$prop	= propFormat($prop, $data, true);
		echo $split, $prop;
		$split = ', ';
	}
}

function prop_read_table(&$props, $cols)
{
	$cols = (int)$cols;
	if ($cols < 1) $cols = 1;
	
	$p = array();
	foreach($props as $name => &$data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;
		$p[] = $data;
	}
	$width	= floor(100/$cols);
	$rows	= floor(count($props) / $cols);
?>
<table border="0" cellspacing="0" cellpadding="0" class="read property">
<? for($row = 0; $row <= $rows; ++$row){
	$class = $row%2?' class="alt"':'';
?>
<tr<?= $class?>>
<? for($col = 0; $col < $cols; ++$col){
	$now	= $p[($col*$rows)+$row];
	$class	= $col?'':' id="first"';
?>
<? if ($col){ ?>
    <td class="split">&nbsp;</td>
<? } ?>
    <th <?= $class?>><?= htmlspecialchars($now['name'])?></th>
    <td <?= $class?>><?= htmlspecialchars($now['property'])?></td>
<? } ?>
</tr>
<? } ?>
</table>
<? } ?>
<? // Module module_prop_sql loaded from  _modules/_module.doc/_module.property/module_prop_sql.php ?>
<?
function module_prop_sql($val, &$ev)
{
	$sql	= &$ev[0];
	$search = &$ev[1];
	//	Найти по родителю
	if (@$val = $search['parent']){
		$search['prop'][':parent'] = alias2doc($val);
	}

	//	Со всеми додкаталогами
	if (@$val = $search['parent*'])
	{
		@list($id, $type) = explode(':', $val);
		$id = alias2doc($id);
		if ($id){
			$db	= module('doc');
			
			if (!is_array($id)) $id = explode(',', makeIDS($id));
			$s	= array();
			$ids= $id;
			while(true){
				$s['prop'][':parent'] = $ids;
				if ($type) $s['type'] = $type;
				$ids = $db->selectKeys('doc_id', doc2sql($s));
				if (!$ids) break;
				$ids = array_diff(explode(',', $ids), $id);
				if (!$ids) break;
				$id = array_merge($id, $ids);
			};
			$search['prop'][':parent'] = implode(', ', $id);
		}else $sql[] = 'false';
	}
	if (isset($search['prop'][':parent']) && !is_array($search['prop'][':parent'])){
		$search['prop'][':parent'] = explode(',', makeIDS($search['prop'][':parent']));
	}

	//	Найти по свойствам
	@$val = $search['prop'];
	if ($val && is_array($val))
	{
		$bHasSQL	= false;
		//	База данных
		$db			= module('prop');
		//	Все условия свойств
		$thisSQL	= array();
		//	Кеш запросов
		$cacheProps	= getCacheValue('propNameCache');
		//	Названия таблиц
		$table		= $db->dbValue->table();
		$table2		= $db->dbValues->table();

		foreach($val as $propertyName => $values)
		{
			if (!is_array($values)) $values = explode(', ', $values);
			if (!$values) continue;
			
			$property	= &$cacheProps[$propertyName];
			if (!isset($property))
			{
				$name = $propertyName;
				makeSQLValue($name);
				$db->open("`name` = $name");
				if ($data = $db->next())
				{
					$queryName	= $data['queryName'];
					$ev			= array(&$data['query'], array());
					if ($queryName) event("prop.query:$queryName", $ev);
					$property	= array($db->id(), $data['name'], $data['valueType'], $ev[1]);
					setCacheValue('propNameCache', $cacheProps);
				}else{
					$property 	= array();
					$thisSQL	= array();
					break;
				}
			}
			
			list($id, $name, $valueType, $query) = $property;
			if ($query){
				foreach($values as &$value){
					$q		= $query[$value];
					$sql[]	= $q?$q:'false';
					$bHasSQL= true;
				}
			}else
			switch($valueType)
			{
				case 'valueDigit':
					foreach($values as &$value) $value = (int)$value;
					$values			= implode(',', $values);
					$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
				break;
				case 'valueText':
					foreach($values as &$value){
						if (!is_string($value)) $value = "$value";
						makeSQLValue($value);
					}
					$values			= implode(',', $values);
					$thisSQL[$id]	= "a$id.`prop_id` = $id AND vs$id.`$valueType` IN ($values)";
				break;
			}
		}

		if ($thisSQL || $bHasSQL){
			foreach($thisSQL as $id => &$s){
				$sql[] 								= $s;
				$sql[':join']["$table AS a$id"]		= "`doc_id` = a$id.`doc_id`";
				$sql[':join']["$table2 AS vs$id"]	= "vs$id.`values_id` = a$id.`values_id`";
			}
		}else $sql[] = 'false';
	}
}
?>
<? // Module module_snippets loaded from  _modules/_module.doc/_module.snippets/module_snippets.php ?>
<?
function module_snippets($fn, &$data)
{
	list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("snippets_$fn");
	return $fn?$fn($val, $data):NULL;
}
function snippets_get(){
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	if (!is_array($snippets)) $snippets = array();

	$snippets2	= getCacheValue('localSnippets');
	if (!is_array($snippets2)) $snippets2 = array();
	
	return array_merge($snippets, $snippets2);
}
function snippets_visual($val, $data){
	return false;
}
function snippets_compile($val, &$data){
	//	[[название сниплета]] => {\{модуль}\}
	$data= preg_replace_callback('#\[\[([^\]]+)\]\]#u', parsePageSnippletsFn, $data);
}
function parsePageSnippletsFn($matches)
{
	$baseCode	= $matches[1];
	$ini		= getCacheValue('ini');
	$snippets	= $ini[':snippets'];
	$code		= $snippets[$baseCode];
	if ($code) return $code;

	@$snippets	= getCacheValue('localSnippets');
	return @$snippets[$baseCode];
}
function snippets_tools($val, $data){
?>
<div style="white-space:nowrap">
Сниппеты: 
<select name="snippets" id="snippets" class="input" onchange="snippetInsert('<?= htmlspecialchars($val)?>', this); ">
<option value="">-- вставить сниппет ---</option>
<?
$snippets = module('snippets:get');
foreach($snippets as $name => $code){ ?>
<option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name)?></option>
<? } ?>
</select>
</div>
<script>
function snippetInsert(name, snippet){
<? if (module('snippets:visual')){ ?>
	var code = '<p class="snippet ' + snippet.value + '">' + "</p>";
<? }else{ ?>
	var code = '[[' + snippet.value + ']]';
<? } ?>
	editorInsertHTML(name, code);
	snippet.selectedIndex = 0;
}
</script>
<? } ?>
<? // Module module_file loaded from  _modules/_module.file/module_file.php ?>
<?
function module_file($val, $data=''){
	//	Попробовать загрузить дополнительный модуль
	@list($val, $v)=explode(':', $val, 2);
	$fn = getFn("file_$val");
	if ($fn) return $fn($v, $data);
}
?>
<? // Module module_gallery loaded from  _modules/_module.gallery/module_gallery.php ?>
<?
function module_gallery($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	if (!$fn) $fn = 'default';
	$fn = getFn("gallery_$fn");
	if (!$fn) return;

	if (!is_array($data) || !$data)
	{
		$id	= (int)$data;
		if (!$id) $id = currentPage();
		if ($id && !defined("galleryShowed$id"))
		{
			define("galleryShowed$id", true);
			module('script:lightbox');
			
			$db	= module('doc');
			$d	= $db->openID($id);
			if (beginCompile($d, "gallery/$val"))
			{
				$d2		= array();
				$d2['src']= $db->folder($id).'/Gallery';
				$fn($val, $d2);
				endCompile($d, "gallery/$val");
			}
			return;
		}
	}
	
	return $fn($val, $data);
}
?>

<? // Module module_links loaded from  _modules/_module.links/module_links.php ?>
<?
$links	= getCacheValue('links');
if (!is_array($links)) reloadLinks();
else{
	$GLOBALS['_SETTINGS']['links']		= getCacheValue('links');;
	$GLOBALS['_SETTINGS']['nativeLink']	= getCacheValue('nativeLink');;
}

function module_links($fn, &$url)
{
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, $url):NULL;
}
function links_getLinkBase(&$db, $val, $url)
{
	$nativeLink	= &$GLOBALS['_SETTINGS']['nativeLink'];
	$u			= strtolower($url);
	return $nativeLink[$u];
}
function links_url(&$db, $val, $url)
{
	$nativeURL	= links_getLinkBase($db, $val, $url);
	if ($nativeURL)
		echo renderURLbase($nativeURL);
}
function links_prepareURL(&$db, $val, &$url)
{
	$links	= &$GLOBALS['_SETTINGS']['links'];
	@$u		= $links[$url];
	if ($u) $url = $u;
}
function reloadLinks()
{
	$db			= module('links');
	$links		= array();
	$nativeLink	= array();
	$db->open();
	while($data = $db->next()){
		$links[$data['nativeURL']]	= $data['link'];
		$nativeLink[$data['link']]	= $data['nativeURL'];
	}
	setCacheValue('links', 		$links);
	setCacheValue('nativeLink', $nativeLink);

	$GLOBALS['_SETTINGS']['links']		= $links;
	$GLOBALS['_SETTINGS']['nativeLink']	= $nativeLink;
}
?>
<?
function links_add(&$db, $val, $url)
{
	$url = preg_replace('#^.*://#',	'', $url);
	$url = preg_replace('#^.*/#',	'', $url);
	$url = preg_replace('#\..*#',	'',	$url);
	$url = preg_replace('#\s+#',	'',	$url);
	if (!$url) return;

	$url = strtolower(trim($url, '/'));
	if ($url) $url = "/$url.htm";
	else $url = '/';
	
	$d = array();
	$d['link']		= $url;
	$d['nativeURL']	= $val;
	$d['user_id']	= 0;
	$iid =  $db->update($d);

	reloadLinks();
	return $iid;
}
?>
<?
function links_delete(&$db, $val)
{
	$db->deleteByKey('nativeURL', $val);
	reloadLinks();
}
?>
<?
function links_get(&$db, $val)
{
	$res = array();
	makeSQLValue($val);
	$db->open("nativeURL = $val");
	while($data = $db->next()){
		$res[$data['link']] = $data['link'];
	}
	return $res;
}
?>


<? // Module module_page loaded from  _modules/_module.page/module_page.php ?>
<?
function module_page($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("page_$fn");
	return $fn?$fn($val, $data):NULL;
}
function module_display(&$val, &$data){
	return page_display($val, $data);
}

function page_header()
{
	?>
    <title><? module("page:title:siteTitle") ?></title>
	<?
	module("page:meta");
	module("page:style");
	module("page:script");
}

function page_title($val, &$data)
{
	if (!$val) $val = 'title';

	@$store = &$GLOBALS['_CONFIG']['page']['title'];
	if (!is_array($store)) $store = array();
	
	if ($data){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
	}else{
		@$title = &$store[$val];
		if ($val == 'siteTitle' && !$title){
			$title	= @$store['title'];
			$ini	= getCacheValue('ini');
			@$seo	= $ini[':SEO'];
			@$seoTitle	= $seo['title'];
			if ($title){
				$title	= $seoTitle?str_replace('%', $title, $seoTitle):$title;
			}else{
				@$title = $seo['titleEmpty'];
			}
		}
		echo htmlspecialchars(strip_tags($title));
		return $title;
	}
}

function page_meta($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['meta'];
	if (!is_array($store)) $store = array();

	if (!$val){
		$ini	= getCacheValue('ini');
		@$seo	= $ini[':SEO'];
		if (is_array($seo)){
			foreach($seo as $name => $val){
				if ($name == 'title' || $name == 'titleEmpty') continue;
				if (isset($store[$name])) continue;
				$store[$name] = $val;
			}
		}
		foreach($store as $name => &$val) page_meta($name, NULL);
		return;
	}
	
	if ($data){
		$store[$val] = is_array($data)?implode(', ', $data):$data;
	}else{
		@$title = &$store[$val];
		if (!$title) return;
		echo '<meta name="', $val, '" content="', htmlspecialchars($title), '" />', "\r\n";
		return $title;
	}
}

function page_display($val, &$data)
{
	if (!$val) $val = 'body';
	if ($bClear = ($val[0] == '!')) $val = substr($val, 1);

	@$store = &$GLOBALS['_CONFIG']['page']['layout'];
	if (!is_array($store)) $store = array();

	if (is_string($data)){
		if ($bClear) $store[$val] = $data;
		else @$store[$val] .= $data;
	}else{
		echo "<!-- begin $val -->\r\n";
		echo @$store[$val];
		if ($bClear) $store[$val] = '';
		echo "<!-- end $val -->\r\n";
	}
}

function page_style($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['styles'];
	if (!is_array($store)) $store = array();

	if ($data){
		if (is_array($data)){
			dataMerge($store, $data);
		}else{
			$store[$data] = $data;
		}
	}else{
		$r = array_reverse($store);
		foreach($r as &$style){
			$s = htmlspecialchars($style);
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$s\"/>\r\n";
		}
	}
}

function page_script($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['scripts'];
	if (!is_array($store)) $store = array();

	if ($val){
		if (is_array($data)){
			dataMerge($store, $data);
		}else{
			$store[$val] = $data;
		}
	}else{
		foreach($store as &$script){
			echo $script, "\r\n";
		}
	}
}
function module_page_access($val, &$content){
	$ini	= getCacheValue('ini');
	$access	= $ini[':siteAccess'];
	if (!$access) return;
	
	$access		= array_keys($access);
	$access[]	= 'admin';
	$access[]	= 'developer';
	if (hasAccessRole($access)) return;
	
	ob_start();
	$config = &$GLOBALS['_CONFIG'];
	$config['page']['layout'] = array();
	setTemplate('login');
	
	switch(getRequestURL())
	{
	case '/user_lost.htm':
	case '/user_login.htm':
	case '/user_register.htm':
		renderPage(getRequestURL());
		break;
	default:
		renderPage('/login.htm');
	}
	
	$content = ob_get_clean();
}
?>
<? // Module module_read loaded from  _modules/_module.page/module_read.php ?>
<?
function module_read($name, $data)
{
	$textBlockName	= "$name.html";
	$filePath		= images."/$textBlockName";
	
	$menu = array();
	if (access('write', "text:$name")){
		$menu['Изменить#ajax_edit']	= getURL("read_edit_$name");
		$menu['Удалить#ajax']		= getURL("read_edit_$name", 'delete');
	};
	
	beginAdmin();
	if (beginCache($textBlockName)){
		@$val = file_get_contents($filePath);
		event('document.compile', $val);
		echo $val;
		endCache($textBlockName);
	}
	endAdmin($menu, $data?false:true);
}

function module_read_access($mode, $data)
{
	switch($mode){
		case 'read': return true;
	}
	return hasAccessRole('admin,developer,writer,SEO');
}
?>

<? // Module module_script loaded from  _modules/_module.script/module_script.php ?>
<?
function module_script($val)
{
	$script = &$GLOBALS['_SETTINGS']['script'][$val];
	if ($script) return;
	$script = true;
	
	$fn = getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	module("page:script:$val", ob_get_clean());
}
function hasScriptUser($val){
	return @$GLOBALS['_SETTINGS']['script'][$val];
}
function isModernBrowser()
{
	$agent		= strtolower($_SERVER['HTTP_USER_AGENT']);
	$browsers	= array("firefox", "opera", "chrome", "safari"); 
	foreach($browsers as $browser){
		if (strpos($agent, $browser)) return true;
	}
	return false;
}
?>
<?
function script_jq($val){
	if (isModernBrowser()) $ver = getCacheValue('jQueryVersion2');
	else $ver = getCacheValue('jQueryVersion');
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery == 'undefined'){  
  document.write('<' + 'script type="text/javascript" src="script/<?= $ver ?>"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/<?= $ver ?>"></script>
<? } ?>

<? function script_jq_ui($val){
	module('script:jq');
	$ini	= getCacheValue('ini');
	$uiTheme= @$ini[':']['jQueryUI'];
	
	$ver	= getCacheValue('jQueryUIVersion');
	if (!$uiTheme) $uiTheme= getCacheValue('jQueryUIVersionTheme');
?>
<link rel="stylesheet" type="text/css" href="script/<?= $ver?>/css/<?= $uiTheme ?>/<?= $ver?>.min.css"/>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof jQuery.ui == 'undefined'){
		$.getScript('script/<?= $ver?>/js/<?= $ver?>.min.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script>
<? } ?>

<? function script_jq_print($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.printElement.min.js"></script>
<script>
/*<![CDATA[*/
	jQuery.browser = {};
	jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
 /*]]>*/
</script>
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cookie.min.js"></script>
<? } ?>

<? function script_overlay($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function(overlayClass) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		var overlay = $('<div id="fadeOverlayLayer" />').appendTo('body')
			.css({
				'position': 'fixed', 'z-index':50,
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
				'opacity': 0.8, 'background': 'black'
				})
			.click(function(){
				$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
			});
		if (overlayClass) $('<div />').addClass(overlayClass).appendTo('body').click(function(){
			$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
			$(this).remove();
		});
		return $('<div id="fadeOverlayHolder" />').appendTo('body').css({'z-index':51});
   };
})( jQuery );
 /*]]>*/
</script>
<? } ?>

<? function script_center($val){ module('script:jq'); ?>
<script type="text/javascript" language="javascript">
(function( $ ) {
	$.fn.center = function() {
		this.css("position","absolute");
		this.css("top",	Math.max(0, (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop()) + "px");
		this.css("left",Math.max(0, (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft()) + "px");
		return this;
	};
})( jQuery );
</script>
<? } ?>

<? function script_calendar($val){ module('script:jq_ui'); ?>
<script type="text/javascript" src="script/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" language="javascript">
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('[id*="calendar"], .calendar').each(function(){
			attachDatetimepicker($(this));
		});
	});
});
function attachDatetimepicker(o){
	o.datetimepicker({
		dateFormat: 	'dd.mm.yy',
		monthNames: 	['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort:['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сент','Окт','Ноя','Дек'],
		dayNamesMin: 	['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
		firstDay: 		1,
		timeOnlyTitle: 'Выберите время',
		timeText: 'Время',
		hourText: 'Часы',
		minuteText: 'Минуты',
		secondText: 'Секунды',
		currentText: 'Теперь',
		closeText: 'Закрыть'
		});
}
</script>
<? } ?>

<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="script/lightbox2.51/css/lightbox.css"/>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
$(function(){
	if (typeof lightbox == 'undefined'){
		$.getScript('script/lightbox2.51/js/lightbox.js');
	}
});
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/lightbox2.51/js/lightbox.js"></script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cross-slide.min.js"></script>
<? } ?>

<? function script_menu($val){ module('script:jq'); ?>
<script type="text/javascript">
//	menu
var menuTimer = 0;
$(function() {
	$('.menu.popup ul li, .menu.popup td').hover(function(){
		popupMenuClose();
		$(this).find("ul").show().css({top: $(this).position().top+$(this).height(), left: $(this).position().left});
	}, function(){
		clearTimeout(menuTimer);
		menuTimer = setTimeout(popupMenuClose, 500);
	});
	$(".menu.popup ul ul li, .menu.popup td li").unbind();
});
function popupMenuClose(){
	$(".menu.popup li ul, .menu.popup td ul").hide();
	clearTimeout(menuTimer);
	menuTimer = 0;
}
</script>
<? } ?>

<? function script_ajaxLink($val){ module('script:overlay'); m('page:style', 'ajax.css') ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('a[id*="ajax"]').click(function(){
			return ajaxLoad($(this).attr('href'), 'ajax=' +  $(this).attr('id'));
		});
		ajaxClose();
		var data = $("#fadeOverlayHolder").attr("rel");
		if (data){
			$(".ajaxDocument .seek a").click(function(){
				return ajaxLoad($(this).attr('href'), data);
			});
		}
	});
});
function ajaxClose(){
	$(".ajaxClose a").click(function()
	{
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		return false;
	});
}
function ajaxLoad(url, data)
{
	$('<div />').overlay('ajaxLoading')
		.css({position:'absolute', top:0, left:0, right:0, bottom: 0})
		.attr("rel", data)
		.load(url, data, function()
		{
			$(".ajaxLoading").remove();
			ajaxClose();
			$(document).trigger("jqReady");
		});
	return false;
}
 /*]]>*/
</script>
<? } ?>
<? function script_ajaxForm($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		//	Отправка через AJAX, только если есть overlay
		$(".ajaxForm").submit(function(){
			if ($('#fadeOverlayHolder').length == 0) return true;
			return submitAjaxForm($(this));
		}).removeClass("ajaxForm").addClass("ajaxSubmit");
		
		$(".ajaxFormNow").submit(function(){
			return submitAjaxForm($(this));
		}).removeClass("ajaxFormNow").addClass("ajaxSubmit");
	});
});

function submitAjaxForm(form, bSubmitNow)
{
	form = $(form);
	if (!bSubmitNow && form.find(".submitEditor").length > 0) return;
	if (("" + form.attr("enctype")).toLowerCase() == "multipart/form-data") return;
	
	var msg = $('#formReadMessage');
	if (msg.length == 0) msg = $('<div id="formReadMessage" class="message work">').insertBefore(form);
	msg.addClass("message work").html("Обработка данных сервером, ждите.");

	if (form.hasClass("ajaxReload") && $('#fadeOverlayHolder').length == 0) return true;
	if (form.hasClass('submitPending')) return;
	form.addClass('submitPending');
	
	var ajaxForm = form.hasClass('ajaxSubmit')?'ajax_message':'';
	if (form.hasClass('ajaxReload')) ajaxForm = 'ajax';

	var formData = form.serialize();
	if (ajaxForm) formData += "&ajax=" + ajaxForm;

	$.post(form.attr("action"), formData)
		.success(function(data){
			form.removeClass('submitPending');
			if (form.hasClass('ajaxReload')){
				$('#fadeOverlayHolder').html(data);
				$(document).trigger("jqReady");
			}else{
				$('#formReadMessage')
					.removeClass("message")
					.removeClass("work")
					.html(data);
			}
		})
		.error(function(){
			form.removeClass('submitPending');
			$('#formReadMessage')
				.removeClass("work")
				.addClass("error")
				.html("Ошибка записи");
		});
	return false;
};
 /*]]>*/
</script>
<? } ?>
<? function script_scroll($val){?>
<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
$(function(){
	$(".scroll").css({"height":$(".scroll table").height(), "overflow":"hidden"})
	.mousemove(function(e)
	{
		//	over
		var cut = 80;
		var thisWidth = $(this).width();
		var width = $(this).find("table").width();
		if (width < thisWidth) return;
		var widthDiff = width - thisWidth;
	
		var percent = (e.pageX - ($(this).offset().left + cut))/(thisWidth - cut*2);
		if (percent < 0) percent = 0;
		if (percent > 1) percent = 1;
		$(this).find("table").css("left", -Math.round(percent*widthDiff));
	});
});
 /*]]>*/
</script>
<? } ?>

<? function script_maskInput($val){ module('script:jq')?>
<script type="text/javascript" src="script/jquery.maskedinput.min.js"></script>
<script>
$(function(){
	$("input.phone").mask("+7(999) 999-99-99");
});
</script>
<? } ?>

<? function script_clone($val){?>
<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
$(function(){
	$("input.adminReplicateButton").click(function(){
		return adminCloneByID($(this).attr('id'));
	}).removeClass("adminReplicateButton");
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
});
function adminCloneByID(id)
{
	var o = $(".adminReplicate#" + id);
	var o2 = o.clone().insertBefore(o).removeClass("adminReplicate");
	$(o2.find(".hasDatepicker")).each(function(){
		$(this).removeClass("hasDatepicker").attr("id", Math.random(20000000));
		attachDatetimepicker($(this));
	});
	
	$(".adminReplicate#" + id + " input").val("");
	$('a.delete').click(function(){
		$(this).parent().parent().remove();
		return false;
	});
}
 /*]]>*/
</script>
<? } ?>



<? // Module module_user loaded from  _modules/_module.user/module_user.php ?>
<?
module('user:enter');
//	module user
function module_user($fn, &$data)
{
	//	База данных пользователей
	$db 		= new dbRow('users_tbl', 'user_id');
	$db->sql	= '`deleted` = 0 AND `visible` = 1';
	$db->images = images.'/users/user';
	$db->url 	= 'user';
	if (!$fn){
		$db->data = $data;
		return $db;
	}
	
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("user_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function module_user_access(&$val, &$data)
{
	list($mode,) = explode(':', $val);
	switch($mode){
	case 'register':
		$ini			= getCacheValue('ini');
		@$denyRegister	= $ini[':user']['denyRegisterNew'];
		return $denyRegister != 1;
	case 'use':
		if ($data[0] != 'adminPanel') return;
		return hasAccessRole('admin,developer,writer,manager,accountManager,SEO');
	}
	if ($data[0]) return false;
	return hasAccessRole('admin,developer,writer,manager,accountManager');
}
function hasAccessRole($checkRole)
{
	@$userRoles	= $GLOBALS['_CONFIG']['user']['userRoles'];
	if (!is_array($checkRole))
		$checkRole = explode(',', $checkRole);
	
	return @array_intersect($userRoles, $checkRole);
}
?>
<? // Module module_user_common loaded from  _modules/_module.user/module_user_common.php ?>
<?
function user_name($db, $val, $data)
{
	$person	= userPerson($data);
	@$name	= $person['name'];
	@$name	= $name['last_name'];
	if (!$name){
		$data	= userData($data);
		@$name	= $data['login'];
		$name	= "<$name>";
	}else{
		if ($val == 'full'){
			@$sName = $person['name'];
			@$sName = $sName['first_name'];
			if ($sName) $name = "$name $sName";
		}
	}
	echo htmlspecialchars($name);
}

function userData($data = NULL){
	if ($data) return $data;
	@$data	= $GLOBALS['_CONFIG']['user'];
	@$data	= $data['data'];
	return $data;
}
function userID($data = NULL){
	$data	= userData($data);
	@$id	= $data['user_id'];
	return (int)$id;
}
function userFields($data = NULL){
	$data	= userData($data);
	@$data	= $data['fields'];
	return $data;
}
function userPerson($data = NULL){
	$data	= userFields($data);
	@$data	= $data['person'];
	return $data;
}
function userLang($data = NULL){
	$data	= userPerson($data);
	@$data	= $data['language'];
	if (!$data) $data = 'ru';
	return $data;
}
?>
<? // Module module_user_enter loaded from  _modules/_module.user/module_user_enter.php ?>
<?
function user_enter($db, $val, &$data)
{
	if (testValue('logout')) user_logout();

	$login = $data?$data:getValue('login');
	//	Проверить регистрацию, если введен логин пользователя
	if (isset($login['login']))
	{	//	Если пользователь регистрируется
		$md5 = getMD5($login['login'], @$login['passw']);
		makeSQLValue($md5);
		$db->open("`md5` = $md5");
		//	Проверка что такой пользователь есть
		//	Если пользователь найден, то регистрация
		if ($data = $db->next()){
			logData("user: \"$data[login]\" entered", 'user');
			define('firstEnter', true);
			return setUserData($db, $login['remember']);
		}
		if ($val) return false;

		user_logout();
		module('message:error', 'Неверный логин или пароль');
		return false;
	}
	
	$md5 = $_COOKIE['userSession5'];
	if ($md5){	//	Если пользователь в сессии, то ищем его в базе
		makeSQLValue($md5);
		$db->open("`md5` = $md5");
		//	Проверка что такой пользователь есть
		if($db->next()){
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	
	//	Если происходит авторегистрация
	$md5 = @$_COOKIE['autologin5'];
	if ($md5){	//	Если пользователь с запоминанием, то ищем его в базе
		makeSQLValue($md5);
		$db->open("`md5` = $md5");
		//	Проверка что такой пользователь есть
		if ($data = $db->next()){
			logData("user: \"$data[login]\" entered", 'user');
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	//	Сбрасываем авторегистрацию
	if ($val) return false;
	user_logout();

	return false;
}
function user_checkLogin($db, $val, $login)
{
	@$md5 = getMD5($login['login'], @$login['passw']);
	makeSQLValue($md5);
	$db->open("`md5` = $md5");
	return $db->next() != NULL;
}

function user_logout()
{
	if (userID()) logData("user: \"$data[login]\" logout", 'user');
//	module('message:user:trace', "User logout from site");
	cookieSet('userSession5', '');
	cookieSet('autologin5', '');
}

//	Регистрация пользователя, установка ACL и прочего
function setUserData(&$db, $remember = false)
{
	$data 	= $db->rowCompact();	//	Получить данные
	$userID = $db->id();			//	Запомнить код
	@define('user', $data['access']);//	Определить уровень доступа
	if ($remember){
		cookieSet('autologin5', $data['md5']);
	}else cookieSet('userSession5', $data['md5'], false);
	
	//	Сохранить данные текущего пользователя
	define('userID', $userID);
	$GLOBALS['_CONFIG']['user']['data']		= $data;
	$GLOBALS['_CONFIG']['user']['userRoles']= explode(',', $data['access']);

//	module('message:user:trace', "User '$data[login]' entered in site");
	return $userID;
}

function getMD5($login, $passw){
	$login = strtolower($login);
	return md5("$login:$passw");
}

?>
<? // Module module_common loaded from  _modules/_module_core/module_common.php ?>
<?
//	Отключить кеширование страниц
function nocache()
{
	if (defined('noCache')) return;
	define('noCache', true);
	
    ini_set('session.cache_limiter', 'nocache'); #добавляем HTTP заголовок Expires
    ini_set('session.cache_expire', 0);          #добавляем HTTP заголовок Cache-Control

    #header('Expires: Thu, 01 Jan 1998 00:00:00 GMT');
    #header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    #динамическая генерация даты, возможно, позволит не "отпугнуть" роботов-индексаторов поисковых систем.
    header('Expires: '       . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', strtotime('-1 day')) . ' GMT');

    # HTTP/1.1
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Cache-Control: max-age=0', false);
    # HTTP/1.0
    header('Pragma: no-cache');
}

function ksortUTF8(&$array){
	$a = array();
	foreach($array as $key => $val){
		$a[iconv('UTF-8', 'windows-1251', $key)] = $val;
	}
	ksort($a);
	$array = array();
	foreach($a as $key => $val){
		$array[iconv('windows-1251', 'UTF-8', $key)] = $val;
	}
}

function removeEmpty(&$src){
	if (!is_array($src)) return;
	foreach($src as $name => &$val){
		removeEmpty($val);
		if (!$val) unset($src[$name]);
	}
}

//	Объеденить массивы
function dataMerge(&$dst, $src)
{
	if (!is_array($src)) return;
	foreach($src as $name => &$val)
	{
		if (is_array($val)){
			if (isset($dst[$name])) dataMerge($dst[$name], $val);
			else $dst[$name] = $val;
		}else{
			if (!isset($dst[$name])) $dst[$name] = $val;
		}
	}
}

function makeNote($val, $nLen = 200)
{
	$nLen	= (int)$nLen;
	$val	= strip_tags($val);
	$val	= preg_replace('#(\s+)#', ' ', $val);
	$val	= trim($val);
	if (!function_exists('mb_strrpos')){
		if (strlen($val) < $nLen) return $val;
		return substr($val, 0, $nLen).' ...';
	}
	
	$minLen	= $nLen - $nLen / 3;
	$val	= mb_substr($val, 0, $nLen);
	if (is_int($nPos = mb_strrpos($val, '.')) && $nPos > $minLen)		$val = mb_substr($val, 0, $nPos+1);
	else if (is_int($nPos = mb_strrpos($val, '!')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1);
	else if (is_int($nPos = mb_strrpos($val, '?')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1);
	$val .= ' ...';
	return $val;
}

function makeQueryString($data, $name = '', $bNameEncode = true)
{
	if ($bNameEncode) $name = urlencode($name);
	if (!is_array($data)) return $name?"$name=$data":$data;

	$v = '';
	foreach($data as $n => &$val)
	{
		if ($v) $v .= '&';
		$n = urlencode($n);
		
		if (is_array($val)){
			$v .= makeQueryString($val, $name?$name."[$n]":$n, false);
		}else{
			if (!preg_match('#^\d+$#', $n)){
				$val = urlencode($val);
				$v  .= $name?$name."[$n]=$val":"$n=$val";
			}else{
				$v  .= $name?$name."[]=$val":"$val";
			}
		}
	}
	return $v;
}
function beginCache($name)
{
	if (!$name) return true;
	
	$cache		= getCacheValue('cache');
	@$thisCache	= $cache[$name];
	if (isset($thisCache)){
		showDocument($thisCache);
		return false;
	}
	ob_start();
	return true;
}

function endCache($name)
{
	if (!$name) return;
	
	$val			= ob_get_clean();
	showDocument($val);
	if (!localCacheExists()) return;
	
	$cache			= getCacheValue('cache');
	$cache[$name]	= $val;
	setCacheValue('cache', $cache);
	module('message:trace', "text cached $name");
}

function setCache($name, $value = NULL)
{
	$cache			= getCacheValue('cache');
	$cache[$name]	= $value;
	if ($value === NULL) unset($cache[$name]);
	setCacheValue('cache', $cache);
}
function getCache($name){
	$cache	= getCacheValue('cache');
	return @$cache[$name];
}
function dbSeek(&$db, $maxRows, $query = array())
{
	ob_start();
	$seek		= seek($db->rows(), $maxRows, $query);
	$db->max	= $maxRows;
	$db->seek($seek);
	return ob_get_clean();
}
function seek($rows, $maxRows, $query)
{
	if (isset($query['search']['url'])) $query = $query['search']['url'];
	
	$pages		= ceil($rows / $maxRows);
	if ($pages < 2) return 0;
	//	Страницы номеруются с 1 по ???
	$thisPage	= min(getValue('page'), $pages);
	$thisPage	= max(1, $thisPage);
	$seek		= $maxRows * ($thisPage - 1);
//	echo "rows: $rows, max: $maxRows, pages: $pages, page: $thisPage, seek: $seek";
	
	$seekEntry	= array();
	$minEntry	= 0;
	$maxEntry	= 20;
	//	Кнопка предыдущая
	if ($thisPage != 1){
		$seekEntry[$minEntry++] = seekLink('&lt;', $thisPage - 1, $query);
	}
	//	Кнопка следующая
	if ($thisPage < $pages){
		$seekEntry[$maxEntry--] = seekLink('&gt;', $thisPage + 1, $query);
	}

	$seekCount	= $maxEntry - $minEntry;
	if ($thisPage - $seekCount/2 < 1){
		for($ix = 0; $ix < $seekCount; ++$ix){
			if ($ix < $pages) $seekEntry[$minEntry + $ix] = seekLink($ix + 1, $ix + 1, $query, $thisPage);
		}
	}else
	if ($thisPage + $seekCount/2 > $pages){
		for($ix = 0; $ix < $seekCount; ++$ix){
			if ($pages - $ix < 1) break;
			$seekEntry[$maxEntry - $ix] = seekLink($pages - $ix, $pages - $ix, $query, $thisPage);
		}
	}else{
		for($ix = 0; $ix < $seekCount; ++$ix){
			$p = floor($thisPage - $seekCount / 2);
			$seekEntry[$minEntry + $ix] = seekLink($p + $ix, $p + $ix, $query, $thisPage);
		}
	}
	ksort($seekEntry);
	
	echo '<div class="seek">';
	echo implode(' ', $seekEntry);
	echo '</div>';
	
	return $seek;
}
function seekLink($title, $page, &$query, $thisPage = NULL){
	$class = $page == $thisPage?' class="current"':'';
	$query['page'] = $page;
	$q	= makeQueryString($query);
	$url= globalRootURL.getRequestURL();
	
	if ($title == $page){
		$v = "<a href=\"$url?$q\"$class>$title</a>";
	}else{
		$v = "<a href=\"$url?$q\"id=\"nav\"$class>$title</a>";
	}
	return $v;
}
?>
<? // Module module_cookie loaded from  _modules/_module_core/module_cookie.php ?>
<?
function cookieSet($name, $val, $bStore = true)
{
	$time = $val && $bStore?time() + 3*7*24*3600:0;
	$_COOKIE[$name] = $val;
	setcookie($name, $val, $time, '');
}
?>

<? // Module module_getURL loaded from  _modules/_module_core/module_getURL.php ?>
<?
function module_getURL($url, &$options){
	echo getURL($url, $options);
}
function module_getURLEx($url, &$options){
	echo getURLEx($url, $options);
}
function module_url($url, &$options){
	echo getURL($url, $options);
}
//	Получить правильную ссылку из пути.
function getURL($url = '', $options = '')
{
	if ($url == '#') $v = getRequestURL();
	else{
		$v		= $url?"/$url.htm":'/';
		event('site.prepareURL', $v);
	}
	$options= is_array($options)?makeQueryString($options):$options;
	return globalRootURL.($options?"$v?$options":$v);
}

function getURLEx($url = '', $options = ''){
	$url	= getURL($url, $options);
	$server = $_SERVER['HTTP_HOST'];;
	return "http://$server$url";
}
?>
<? // Module module_message loaded from  _modules/_module_core/module_message.php ?>
<?
//	message, message:error, message:sql
function module_message($val, &$data)
{
	if ($val == '' || $val == 'error')
	{
		if (is_array($data)) $data = implode(' ', $data);
		$data = rtrim($data);
		if (!$data) return;
		$messageClass = $val?'message error':'message';
		return module('page:display:message', "<div class=\"$messageClass shadow\">$data</div>");
	}
	
	if (is_array($data)){
		ob_start();
		print_r($data);
		$data = ob_get_clean();
	}
	
	$data = rtrim($data);
	if (!$data) return;
	
	$hasError	= strpos($val, 'error');
	$class		= $hasError?' class="errorMessage"':'';
	@list($val, $type)	= explode(':', $val);
	if (!$type)$type= $val;
	
	switch($val){
	case 'sql':
		$val	= 'logSQL';
	break;
	case 'trace':
		$val	= 'logTrace';
	break;
	default:
		$val	= 'log';
	}
	module("page:display:$val", "<span$class>$type: <span>$data</span></span>\r\n");
	if ($hasError) module("page:display:log", "<span$class>$type: <span>$data</span></span>\r\n");
}
function messageBox($message){
	if (!$message) return;
	echo "<div class=\"message\">$message</div>";
}
?>
<? // Module module_libFile loaded from  _modules/_module_core/_lib/module_libFile.php ?>
<?
function echoEncode($value){
	echo $value;//iconv("windows-1251", "utf-8", $value);
}
//	Вывести на экран массив, как XML документ
//	Использовать знак '@' в дочерних нодах для записи как аттрибуты
function writeXML(&$xml, $date = NULL){
	// Prevent the browser from caching the result.
	if (!$date){
		// Date in the past
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
		// HTTP/1.1
		header('Cache-Control: no-store, no-cache, must-revalidate') ;
		header('Cache-Control: post-check=0, pre-check=0', false) ;
		// HTTP/1.0
		header('Pragma: no-cache') ;
		// always modified
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
	}else{
		//	Дата изменения
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $date) . ' GMT') ;
	}
	// Set the response format.
	header( 'Content-Type:text/xml; charset=UTF-8' ) ;
	echoEncode('<?xml version="1.0" encoding="UTF-8"?>');

	writeXMLtag($xml);
}

function writeXMLtag(&$xml){
//	while(list($tag, $child)=each($xml)){
	foreach($xml as $tag => &$child){
		if (is_int($tag)){
			writeXMLtag($child);
			continue;
		}
		if (!is_array($child)){
			if ($tag[0] == '!'){
				$tag = substr($tag, 1);
				echoEncode("<$tag><![CDATA[$child]]></$tag>");
			}else{
				echoEncode("<$tag>".htmlspecialchars($child)."</$tag>");
			}
			continue;
		}
		
		$tags = array();
		echoEncode("<$tag");
//		while(list($name, $value)=each($child)){
		foreach($child as $name => &$value){
			if ($name[0] != '@'){
				$tags[$name] = $value;
				continue;
			}
			$name	= substr($name, 1);
			$name	= $name;
			$valu	= htmlspecialchars($value);
			echoEncode(" $name=\"$value\"");
		}
		if ($tags){
			echoEncode(">");
			writeXMLtag($tags);
			echoEncode("</$tag>");
		}else echoEncode("/>");
	}
}
function utf8_to_win($string){
	$out = '';
	for ($c=0;$c<strlen($string);$c++){
		$i=ord($string[$c]);
		if ($i <= 127) @$out .= $string[$c];
		if (@$byte2){
			$new_c2=($c1&3)*64+($i&63);
			$new_c1=($c1>>2)&5;
			$new_i=$new_c1*256+$new_c2;
			if ($new_i==1025){
				$out_i=168;
			} else {
				if ($new_i==1105){
					$out_i=184;
				} else {
					$out_i=$new_i-848;
				}
			}
			@$out .= chr($out_i);
			$byte2 = false;
		}
		if (($i>>5)==6) {
			$c1 = $i;
			$byte2 = true;
		}
	}
	return $out;
}
//	Нормализовать путь к файлу, преобразовать в транслит
function makeFileName($name, $fromUTF = false){
/*
	if ($fromUTF){
		if (function_exists('iconv')) $name = iconv('UTF-8', 'windows-1251', $name);
		elseif (function_exists('mb_convert_encoding')) $name = mb_convert_encoding($name, 'UTF-8', 'windows-1251');
		else $name = utf8_to_win($name);
	}
*/	moduleEx('module_translit', $name);
	$name = urlencode($name);
	return preg_replace('#%[0-9A-Fa-f]{2}#', '-', $name);
}
//	Нормальизовать путь
function normalFilePath($name){
	$name = preg_replace('#[.]{2,}#', '.', $name);
	$name = preg_replace('#[/]{2,}#', '/', $name);
	$name = preg_replace('#[./]{2,}#','',  $name);
	return trim($name, '/');
}
//	Определить, можно ли редактировать папку с файлами или файл
function canEditFile($path){
	return true;
}
//	Определить что файл можно прочитать
function canReadFile($path){
	return true;
}


?>
<? // Module module_libImage loaded from  _modules/_module_core/_lib/module_libImage.php ?>
<?
if (!extension_loaded('gd'))	dl('gd.so') || dl('gd2.dll');
if (function_exists('imagecreatetruecolor')) 
	define('gd2', true);
////////////////////////////
//	Обработка комманд файлов
function modFileAction($baseDir, $bClearBaseDir = false)
{
	$clear	= false;
	$modFile= getValue('modFile');
	removeSlash($modFile);
	$baseDir .= '/';

	//	Файлы для удаления:
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Кнопка: <input type="submit" name="modFile[delButton]">
	$delFiles = array();
	if (!@$modFile) $modFile = array();
	
	if (@$modFile['delButton'] && @is_array($modFile['files']))
		$delFiles = array_merge($delFiles, $modFile['files']);
		
	if (@is_array($modFile['delete']))
		$delFiles = array_merge($delFiles, $modFile['delete']);

	if ($delFiles){
//	print_r($delFiles);
		//	Просмотреть список папок с файлами
		while(@list($folder, $val)=each($delFiles)){
			//	Удалить файл из папки
			if (is_array($val)){
				while(list($ndx, $file)=each($val)){
					if (is_int($ndx))
						unlinkFile($baseDir.normalFilePath("$folder/$file"));
					else
						unlinkFile($baseDir.normalFilePath("$folder/$ndx"));
					$clear = true;
				}
			}else{
				unlinkFile($baseDir.normalFilePath("$folder/$val"));
				$clear = true;
			}
		}
	}

	//	Файлы для загрузки:
	//	Список: <input type="file" name="modFileUpload[folder name]['' или 'file name']">
	@$fileUpload = $_FILES['modFileUpload'];
	$bFirstEntry = true;
	//	Просмотреть названия файлов для загрузки по именам
	while(@list($folder, $val)=each($fileUpload['name'])){
		//	Получить индекс файла и его реальное имя
		while(list($ndx, $srcName)=each($val)){
			//	Получить временное имя файла на компьютере
			$tmp = $fileUpload['tmp_name'][$folder][$ndx];
			//	Если файл не закачен, то пропустить
			if (!$tmp) continue;
			//	Ограничить размер заливаемого файла
//			if (!is_writer() && filesize($tmp) > 5*1024*1024) continue;
			//	Если индекс файла не цифра а текстовое поле, то присвоить новое имя файла
			if (!is_int($ndx) && (int)$ndx==0){
				$ext = explode('.', $srcName);
				$ext = strtolower(array_pop($ext));
				$srcName="$ndx.$ext";
			}
			$path = "$folder/$srcName";
			//	Удалить предыдущий файл с таким же названием, если он есть
			unlinkFile($baseDir.normalFilePath($path));
			//	Убрать все левые символы
			$srcName= normalFilePath(makeFileName($srcName));
			$path 	= $baseDir.normalFilePath("$folder/$srcName");
			//	Удалить папку назначения, если задано
			if ($bFirstEntry && $bClearBaseDir){
				$bFirstEntry = false;
				delTree(dirname($path));
			}
			//	Создать папку для размещения файла
			if (is_file(dirname($path))) @unlink(dirname($path));
			createFileDir(dirname($path));
			//	Переместить файл
//			echo $tmp, ' ', $path;
			move_uploaded_file($tmp, $path);
			//	Задать аттрибуты доступа на чтение
			fileMode($path);
			//	Добавить в список отмеченных файлов
			$modFile['files'][$folder][]=$srcName;
			$clear = true;
		}
	}
	//	Файлы для изменения размеров
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Кнопка: <input type="submit" name="modFile[sizeButton]">
	if (@$modFile['sizeButton']){
		//	Просмотреть все папки с файлами
		@reset($modFile['files']);
		while(@list($folder, $val)=each($modFile['files'])){
			//	Изменить размер каждого файла по зпдпнным параметрам
			while(list($ndx, $file)=each($val)){
				$file = "$baseDir/".normalFilePath("$folder/$file");
				resizeImage($file, $modFile['sizeW'], $modFile['sizeH']);
				$clear = true;
			}
		}
	}
	//	Установить комментарии для файлов, или нажата кнопка или имеется комментарий
	//	Список: <input type="checkbox" name="modFile[files][]" value="file name'>
	//	Комменткарий: <input type="text" name="modFile[comment]">
	//	Кнопка: <input type="submit" name="modFile[commentButton][?file]">
	if (@$modFile['commentButton'] || @$modFile['comment']){
//		@$modFile['comment'] = stripslashes($modFile['comment']);
		//	Просмотреть все папки с файлами
		@reset($modFile['files']);
		while(@list($folder, $val)=each($modFile['files'])){
			$path = "$baseDir/$folder";
			//	Просмотреть каждый файл
			while(list($ndx, $fileName)=each($val)){
				$file = normalFilePath("$path/$fileName");
				//	Если файла нет, то пропустить
				if (!is_file($file)) continue;
				//	Если есть комментарий, то задать новый иначе удалить файл
				if (is_array($modFile['comment'])){
					if (!isset($modFile['comment'][$fileName])) continue;
					$comment = $modFile['comment'][$fileName];
				}else $comment = $modFile['comment'];
				
				if ($comment) file_put_contents_safe("$file.shtm", $comment);
				else @unlink("$file.shtm");
	
				$clear = true;
			}
		}
	}
	return $clear;
}

//////////////////////////////////////////////////////
//	Различные общие функции
/////////////////////////////////////////////////////
function isMaxFileSize($path)
{
	if (!$path) return true;
	m("message:trace", "Read image $path");

	if (!defined('gd2')) return true;
	@list($w,$h) = getimagesize($path);
	if (!$w || !$h) return true;
	if ($w*$h < 1500*1500*3) return false;

	m("message:error", "Big image size $path");
	return true;
}
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath='')
{
	if (isMaxFileSize($srcPath)) return false;
	//	Задать путь для записи результата
	if (!$dstPath) $dstPath = $srcPath;
	//	Получит размер загруженного изображения
	@list($iw, $ih) = getimagesize($srcPath);
	if (!$iw || !$ih) return false;
	//	Прменить трансформацию
	//	Если установлены оба размера, изменить по минимальным размерам
	if ($w > 0 && $h > 0){
		$zoom = ($iw>$ih)?$w/$iw:$h/$ih;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg= imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else
	//	Если установлена ширина, то сохранить пропорцию по высоте
	if ($w > 0){
		$zoom = $w/$iw;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg = imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		@imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else
	//	Если установлена высота, то сохранить пропорцию по ширине
	if ($h > 0){
		$zoom = $h/$ih;
		$w = $iw*$zoom;	$h = $ih*$zoom;
		if (!checkResize($srcPath, $dstPath, $iw, $ih, $w, $h)) return false;
		$jpg = loadImage($srcPath);
		$dimg = imagecreatetruecolor($w, $h);
		$bgc = imagecolorallocate ($dimg, 255, 255, 255);
		imagefilledrectangle ($dimg, 0, 0, $w, $h, $bgc);
		@imagecopyresampled($dimg, $jpg, 0, 0, 0, 0, $w, $h, $iw, $ih);
	}else return false;

	makeDir(dirname($dstPath));
	list($file, $ext)=fileExtension($dstPath);
	switch(strtolower($ext)){
	case 'jpg':	$b = imagejpeg($dimg,$dstPath, 90);	break;
	case 'png':	$b = imagepng($dimg, $dstPath);		break;
	case 'gif':	$b = imagegif($dimg, $dstPath);		break;
	default: return false;
	}
	chmod($dstPath, 0755);
	makeDir(dirname($dstPath));
	return $b;
}
function checkResize($src, $dst, $iw, $ih, $w, $h){
	if ($src==$dst && $iw==$w && $ih==h) return false;
	if ($src!=$dst && is_file($dst)){
		@list($iw, $ih)=getimagesize($dst);
		if ($iw==$w && $ih==h) return false;
	}
	return true;
}
function  loadImage($src)
{
	list($file, $ext) = fileExtension($src);
	$img = NULL;
	switch(strtolower($ext)){
	case 'jpg':	@$img = imagecreatefromjpeg($src);	break;
	case 'png':	@$img = imagecreatefrompng($src);	break;
	case 'gif':	@$img = imagecreatefromgif($src);	break;
	}
	if (!$img) @$img = imagecreatefromjpeg($src);
	if (!$img) @$img = imagecreatefrompng($src);
	if (!$img) @$img = imagecreatefromgif($src);
	return $img;
}
function createFileDir($path){
	$dir='';
	$path=explode('/',str_replace('\\', '/', $path));
	while(list(,$name)=each($path))	@mkdir($dir.="$name/");
}
//	Получить список файлов по фильтру
function getFileList($dir, $filter, $isFiles=true){
	@$d=opendir($dir);
	$files = array();
	while((@$file=readdir($d))!=NULL){
		$f = "$dir/$file";
		if (!preg_match("#$filter#", $file)) continue;
		if ($isFiles){
			if (!is_file($f)) continue;
		}else{
			if ($file=='.' || $file=='..' || !is_dir($f)) continue;
		}
		$files[$file]=$f;
	}
	@closedir($d);
	ksort($files);
	return $files;
}
//	Удалить файл со всеми возможными сопровождающими данными
function unlinkAutoFile($path){
	//	Удалить расширение файла
	list($file,) = fileExtension(basename($path));
	//	Получтить все папки с миникартинками
	$path = dirname($path);
	$thumbs = getFileList($path, '^thumb', false);
	//	Удалить все миникартинки файла
	while(list($ndx, $path)=each($thumbs)){
		@unlink("$path/$file.jpg");	// Удалить миникартинку
		@rmdir($path);				// Удалить пустую папку
	}
}
function unlinkFile($path){
	@unlink($path);			//	Удалить сам файл
	@unlink("$path.shtml");	//	Удалить комментарий к файлу
	unlinkAutoFile($path);
}
//	Получить расширение файла
function fileExtension($path){
	$file = explode('.', $path);
	$ext = array_pop($file);
	return array(implode('.', $file), $ext);
}
//	
function displayThumbImage($src, $w, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;

	$dir = dirname($src);
	list($file,) = fileExtension(basename($src));
	$wName = $w;
	if (is_array($w)){
		@list($w, $h) = $w;
		if (!@list($iw, $ih) = getimagesize($src)) return;

		$wName= $w.'x'.$h;
		$zoom = ($iw>$ih)?$w/$iw:$h/$ih;
		if ($iw > $ih && $ih*$zoom < $h){
			$h = 0;
			if ($iw <= $w) return displayImage($src, $options, $altText);
		}else{
			$w = 0;
			if ($ih <= $h) return displayImage($src, $options, $altText);
		}
	}else $h = 0;
	
	$dst = "$dir/thumb$wName/$file.jpg";
	if (!file_exists($dst) && !resizeImage($src, $w, $h, $dst)) return false;
	
	list($w, $h) = getimagesize($dst);

	$dst 	= imagePath2local($dst);
	$dst	= htmlspecialchars($dst);
	if (!$altText) $altText = @file_get_contents("$src.shtm");
	$altText	= htmlspecialchars($altText);
	$options	.= " alt=\"$altText\"";
	
	$ctx = "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	
	return $dst;
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function displayThumbImageMask($src, $maskFile, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;

	$maskFile	= localCacheFolder."/siteFiles/$maskFile";
	$dir		= dirname($src);
	list($file,) = fileExtension(basename($src));
	$m 		= basename($maskFile, '.png');
	$dst 	= "$dir/thumb_$m/$file.jpg";
	//	Если файла с маской нет, сделать его
	@list($w, $h) = getimagesize($dst);
	if (!$w || !$h){
		//	Получаем размеры изображений
		$mask = @imagecreatefrompng($maskFile);
		if (!$mask)	return false;
		
		//	Загружаем файл с маской
		$jpg = loadImage($src);
		if (!$jpg) return false;
		
		$w = imagesx($mask);$h = imagesy($mask);
		$iw= imagesx($jpg);	$ih= imagesy($jpg);
		
		//	Определить соосность картинок, выбрать маску с нужной ориентацией
		if (($w < $h) != ($iw < $ih)){
			$dir = dirname($maskFile);
			$file= basename($maskFile);
			$rMask = "$dir/r-$file";
			@list($rw, $rh) = getimagesize($rMask);

			if ($rw && $rh){
				$mask = @imagecreatefrompng($rMask);
				$w = $rw; $h = $rh;
			}
		}
		//	Определяем конечные размеры картинки для масштабирования
		$zoom	= $w/$iw;
		$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
		//	Если пропорции не совпадают, сменить плоскость масштабирования
		if ($cw < $w || $ch < $h){
			$zoom	= $h/$ih;
			$cw		= round($iw*$zoom); $ch = round($ih*$zoom);
		}
		//	СОздать базовую картинку
		$dimg = imagecreatetruecolor($w, $h);
		//	Скопировать изображение
		$cx = round(($cw-$w)/2);
		imagecopyresampled($dimg, $jpg, 0, 0, $cx, 0, $cw, $ch, $iw, $ih);
		//	Наложить маску
		imagecopy($dimg, $mask, 0, 0, 0, 0, $w, $h);
		//	Сохранить картинку
		makeDir(dirname($dst));
		imagejpeg($dimg, $dst, 90);

		chmod($dst, 0755);
		makeDir(dirname($dst));
		
		//	Удалить временные картинки
		imagedestroy($mask);
		imagedestroy($jpg);
		imagedestroy($dimg);
	}
	//	Вывести на экран
	$dst 	= imagePath2local($dst);

	$d = $dst = htmlspecialchars($dst);
	if (!$altText) $altText = @file_get_contents("$src.shtm");
	$altText = htmlspecialchars($altText);
	$options .= " alt=\"$altText\"";
	
	$ctx =  "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	return $d;
}
function displayImage($src, $options='', $altText=''){
	if (isMaxFileSize($src)) return false;

	@list($w, $h) = getimagesize($src);
	if (!$w || !$h) return false;

	$src 	= imagePath2local($src);
	$altText= htmlspecialchars($altText);
	$altText= " alt=\"$altText\"";
	echo "<img src=\"$src\" width=\"$w\" height=\"$h\"$altText$options />";
	return true;
}
function showPopupImage($src, $showFullUrl, $ctx, $alt='', $rel='')
{
	module('script:lightbox');
	$rel 		= $rel?"lightbox[$rel]":'lightbox';
	$showFullUrl= imagePath2local($showFullUrl);
	echo "<a href=\"$showFullUrl\" class=\"zoom\" title=\"$alt\" target=\"image\" rel=\"$rel\">", $ctx, "<span></span></a>";
}
function imagePath2local($src){
	$src		= str_replace(globalRootURL.'/'.localHostPath.'/',	'', globalRootURL."/$src");
	$src		= str_replace('/'.localHostPath.'/', 				'', globalRootURL."/$src");
	return $src;
}
function clearThumb($folder){

	$files = getFileList($folder, '^thumb', false);
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getFileList($folder, '', false);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>
<? // Module module_log loaded from  _modules/_module_core/_log/module_log.php ?>
<?
function logData($message, $source = '', $data = '')
{
	$db	= new dbRow('log_tbl', 'log_id');
	
	$d	= array();
	$d['user_id']	= userID();
	$d['userIP']	= userIP();
	$d['session']	= sessionID;
	$d['date']		= makeSQLDate(time());
	
	$d['message']	= $message;
	$d['source']	= $source;
	$d['data']		= serialize($data);
	
	foreach($d as $name => &$val) makeSQLValue($val);
	$db->insertRow($db->table, $d, true);
}
?>
<? // Module module_gzip loaded from  _modules/_module_core/_module.gzip/module_gzip.php ?>
<?
function module_gzip($val, &$Contents)
{
    if ($ENCODING = CheckCanGzip()){
        header("Content-Encoding: $ENCODING"); 
        print "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
        $Size	= strlen($Contents); 
        $Crc	= crc32($Contents); 
        $Contents	= gzcompress($Contents, 3); 
        $Contents	= substr($Contents,  0,  strlen($Contents) - 4); 
		$Contents	.=pack('V', $Crc).pack('V', $Size);
    }
}

function CheckCanGzip()
{
	$ini = getCacheValue('ini');
	if (@$ini[':']['compress'] != 'gzip') return;

    @$HTTP_ACCEPT_ENCODING = $_SERVER['HTTP_ACCEPT_ENCODING']; 
    if (headers_sent() || connection_aborted()){
        return; 
    }
    if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false)	return "x-gzip"; 
    if (strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== false) 	return "gzip"; 
    return; 
}
?>
<? // Module module_database loaded from  _modules/_module_database/module_database.php ?>
<?
class dbConfig
{
	var		$dbLink;
	var		$ini;
	var		$connected;
	var		$dbCreated;
	function create($dbLink = NULL){
		return $this->dbLink 	= $dbLink?$dbLink:new MySQLi();
	}
	function getConfig()
	{
		if (isset($this->ini)) return $this->ini;
		//	Смотрим локальные настройки базы данных
		$ini		= getCacheValue('ini');
		$dbIni		= $ini[':db'];
		//	Если их нет, пробуем глобальные
		if (!is_array($dbIni)){
			//	Получим глобальные правила
			$globalDb	= $ini[':globalSiteDatabase'];
			if (!is_array($globalDb)){
				$ini		= getGlobalCacheValue('ini');
				//	Получим глобальные правила
				$globalDb	= $ini[':globalSiteDatabase'];
				if (!is_array($globalDb)) $globalDb = array();
			}
			//	Пройдемся по правилам
			foreach($globalDb as $rule => $dbKey){
				if (!preg_match("#$rule#i", $_SERVER['HTTP_HOST'])) continue;
				//	Если правило подходит, возмем значение из нового ключа
				$dbIni	= $ini[$dbKey];
				break;
			}
			//	Если настроек не найдено, пробуем стандартные
			if (!is_array($dbIni))
				$dbIni = $ini[':db'];
		}
		return $this->ini = $dbIni;
	}
	function dbConnect($bCreateDatabase = false)
	{
		return $this->dbConnectEx($this->getConfig(), $bCreateDatabase);
	}
	function dbConnectEx($dbIni, $bCreateDatabase = false)
	{
		if (!$this->connected){
			$ini		= getGlobalCacheValue('ini');
			$pConnect	= $ini[':']['mySQLpconnect'];
			
			$dbhost	= $dbIni['host'];
			$dbuser	= $dbIni['login'];
			$dbpass	= $dbIni['passw'];
			$db		= $dbIni['db'];
		
			$timeStart	= getmicrotime();
			$cnn		= NULL;
			if ($pConnect)	$cnn = $this->dbLink->connect($dbhost, $dbuser, $dbpass);
			if (!$cnn)		$cnn = $this->dbLink->connect($dbhost, $dbuser, $dbpass);
			$time 		= round(getmicrotime() - $timeStart, 4);

			if (!defined('restoreProcess')){
				module('message:sql:trace', "$time CONNECT to $dbhost");
				module('message:sql:error', $this->dbLink->error);
			}
			if ($this->dbLink->error){
				module('message:sql:error', $this->dbLink->error);
				module('message:error', 'Ошибка открытия базы данных.');
				return;
			}
		}
		if ($bCreateDatabase && $this->dbCreated){
			$this->dbExec("CREATE DATABASE `$db`");
			$this->dbCreated = true;
		}
		if ($this->connected) return;
	
		$this->dbExec("SET NAMES UTF8");
		$this->dbSelect($db);
		$this->connected = true;
		
		return true;
	}
	function dbTablePrefix()
	{
		$dbConfig	= $this->getConfig();
		$prefix		= $dbConfig['prefix'];
		
		$constName	= "tablePrefix_$prefix";
		if (defined($constName)) return constant($constName);

		$url		= preg_replace('#[^\d\w]+#', '_', getSiteURL());
		if (!$prefix) $p = $url.'_';
		else $p = "$url_$prefix".'_';
		define($constName, $p);
		return $p;
	}
	function dbTableName($name){
		$prefix = $this->dbTablePrefix();
		return "$prefix$name";
	}
	function dbExec($sql, $rows=0, $from=0, &$dbLink = NULL)
	{
		if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";
	
		$timeStart	= getmicrotime();
		$res		= $this->dbLink->query($rows?"$sql LIMIT $from, $rows":$sql);
		$time 		= round(getmicrotime() - $timeStart, 4);
	
		if (!defined('restoreProcess')){
			module('message:sql:trace', "$time $sql");
			module('message:sql:error', $this->dbLink->Error);
		}
	
		return $res;
	}
	function dbSelect($db)			{ return $this->dbLink->select_db($db); }
	function dbRows($id)			{ return $this->dbLink->affected_rows; }
	function dbResult($id)			{ return $id?$id->fetch_array(MYSQLI_ASSOC):NULL;}
	function dbRowTo($id, $row)		{ return $id?$id->data_seek($row):NULL; }
	function dbExecIns($sql, $rows = 0){
		$this->dbExec($sql, $rows, 0);
		return $this->dbLink->insert_id;
	}
	function dbExecQuery($sql){ 
		$err= array();
		$q	= explode(";\r\n", $sql);
		while(list(,$sql)=each($q)){
			if (!$sql) continue;
			if ($this->dbExec($sql, 0, 0)) continue;
			$e 		= $this->dbLink->error;
			$err[] 	= $e;
		}
		return $err;
	}
	function escape_string($val){
		$val = $this->dbLink->escape_string($val);
		return $val;
	}
};

class dbRow
{
	var $dbLink;
//	main functions
	function dbRow($table = '', $key = '', $dbLink = 0)
	{
		if (!$dbLink) $dbLink = $GLOBALS['_CONFIG']['dbLink'];
		if (!$dbLink){
			$dbLink	= new dbConfig();
			$dbLink->create();
			$dbLink->dbConnect();
			$GLOBALS['_CONFIG']['dbLink']	= $dbLink;
		}
		$this->dbLink	= $dbLink;
		$this->table	= $this->dbLink->dbTableName($table);;
		$this->max		= 0;
		$this->key 		= $key;
	}
	function escape_string($val){
		return $this->dbLink->escape_string($val);
	}
	function error(){
		return $this->dbLink->Error;
	}
	function reset()		{
		$this->order = $this->group = $this->fields = '';
	}
	function setCache(){
		if (!isset($this->cache)){
			$cache	= &$GLOBALS['_CONFIG'];
			$cache	= &$cache['dbCache'];
			$cache	= &$cache[$this->table];
			if (!isset($cache)) $cache = array();
			$this->cache = &$cache;
		}
	}
	function setCacheData($id, &$data){
		if (isset($this->cache)) $this->cache[$id] = $data;
	}
	function resetCache($id){
		if (isset($this->cache)) $this->cache[$id] = NULL;
	}
	function clearCache($id = NULL){
		if (isset($this->cache)){
			if ($id) $this->cache[$id] = NULL;
			else $this->cache = array();
		}
	}
	function open($where='', $max=0, $from=0, $date=0)
	{
		return $this->exec($this->makeSQL($where, $date), $max, $from);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		if ($ids){
			$key 	= makeField($this->key());
			return $this->open("$key IN ($ids)");
		}
		return $this->open('false');
	}
	function openID($id)
	{
		$id		= (int)$id;
		if (isset($this->cache)){
			$data = $this->cache[$id];
			if (isset($data)) return $data;
		}
		
		$key	= makeField($this->key());
		$this->open("$key = $id");
		$data	= $this->next();
		
		if (isset($this->cache)) $this->cache[$id] = $data;
		return $data;
	}

	function delete($id){
		$table	=	$this->table();
		$key 	=	$this->key();
		$id		=	makeIDS($id);
		$key 	=	makeField($key);
		$table	=	makeField($table);
		$this->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	function deleteByKey($key, $id){
		$key	= makeField($key);
		$table	= $this->table();
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $this->exec($sql);
	}
	function sortByKey($sortField, &$orderTable, $startIndex = 0)
	{
		if (!is_array($orderTable)) return;
		
		$sortField	= makeField($sortField);
		$key		= $this->key();
		$table		= $this->table();

		$nStep	= (int)$startIndex;
		$sql	= '';
		foreach($orderTable as $id){
			$nStep += 1;
			makeSQLValue($id);
			$this->exec("UPDATE $table SET $sortField = $nStep WHERE $key = $id");
		}
	}
	function selectKeys($key, $sql = '')
	{
		$ids			= array();
		$key			= makeField($key);
		$this->fields	= "$key AS id";
		$sql[]			= $this->sql;
		$res			= $this->dbLink->dbExec($this->makeSQL($sql), 0, 0);
		while($data = $this->dbLink->dbResult($res)) $ids[] = $data['id'];
		return implode(',', $ids);
/*
		$key	=	makeField($key);
		$this->fields	= "GROUP_CONCAT(DISTINCT $key SEPARATOR ',') AS ids";
		$res	= dbExec($this->makeSQL($sql), 0, 0, $this->dbLink);
		$data	= dbResult($res);
		return $data['ids'];
*/
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function execSQL($sql)	{ return $this->dbLink->dbExec($sql, 0, 0); }
	function exec($sql, $max = 0, $from = 0){
		$this->maxCount = $this->ndx = 0;
		return $this->res = $this->dbLink->dbExec($sql, $max, $from);
	}
	function next(){ 
		if ($this->max && $this->maxCount >= $this->max) return false;
		$this->maxCount++;
		$this->ndx++;
		$this->data = $this->dbLink->dbResult($this->res);
		return $this->rowCompact();
	}
	function rows()			{ return $this->dbLink->dbRows($this->res); }
	function seek($row)		{ $this->dbLink->dbRowTo($this->res, $row); }
	function id()			{ return $this->data[$this->key()]; }
	function makeSQL($where, $date = 0)	{
		$sql = $this->makeRawSQL($where, $date);
		$sql['from']	= "FROM $sql[from]";
		return implode(' ', $sql);
	}
	function makeRawSQL($where, $date = 0)
	{
		if (!is_array($where)) $where = $where?array($where):array();
		
		$join		= '';
		$thisAlias	= '';
		$table		= makeField($this->table());
		$group		= $this->group;

		if ($this->fields) $fields = $this->fields;
		else $fields = '*';

		if ($val = $where[':from'])
		{
			unset($where[':from']);

			$t = array();
			foreach($val as $name => $alias){
				if (is_int($name)){
					$t[]		= "$table AS $alias";
					$thisAlias	= $alias;
				}else{
					$name		= $this->dbLink->dbTableName($name);
					$t[]		= "$name AS $alias";
				}
			}
			$table = implode(', ', $t);
		}
		if ($val = $where[':fields']){
			unset($where[':fields']);
			$fields = $val;
		}
		if ($val = $where[':group']){
			unset($where[':group']);
			$group = $val;
		}
		if ($val = $where[':join'])
		{
			unset($where[':join']);
			foreach($val as $joinTable => $joinWhere){
				$join  .= "INNER JOIN $joinTable ON $joinWhere";
			}
		}
		if ($this->sql)
			$where[] .= $this->sql;
			
		if ($date)
			$where[]	= 'lastUpdate > '.makeSQLDate($date);
		
		$where = implode(' AND ', $where);
		
		if ($where) $where = "WHERE $where";
		if ($order = $this->order) $order = "ORDER BY $order";
		if ($group)	$group = "GROUP BY $group";
		
		//	Заменить названия полей на название с алиасом
		if ($thisAlias)
		{
			$fields	= preg_replace('#(\s|^)\*#',	"\\1$thisAlias.*",	$fields);
			
			$r = '#([\s=(]|^)(`[^`]*`)#';
			$fields	= preg_replace($r, "\\1$thisAlias.\\2" ,$fields);
			$join	= preg_replace($r, "\\1$thisAlias.\\2", $join);
			$where	= preg_replace($r, "\\1$thisAlias.\\2", $where);
			$group	= preg_replace($r, "\\1$thisAlias.\\2", $group);
			$order	= preg_replace($r, "\\1$thisAlias.\\2", $order);
		}

		$sql 			= array();
		$sql['action']	= 'SELECT';
		$sql['fields']	= $fields;
		$sql['from']	= $table;
		$sql['join']	= $join;
		$sql['where']	= $where;
		$sql['group']	= $group;
		$sql['order']	= $order;
		return $sql;
	}
	
	function rowCompact()
	{
		if ($this->data['fields'] && !is_array($this->data['fields'])){
			$a = unserialize($this->data['fields']);
			if (is_array($a)) $this->data['fields'] = $a;
		}
		if ($this->data['document'] && !is_array($this->data['document'])){
			$a = unserialize($this->data['document']);
			if (is_array($a)) $this->data['document'] = $a;
		}
		reset($this->data);

		if (isset($this->cache)){
			$id	= $this->data[$this->key];
			$this->cache[$id] = $this->data;
		}

		return $this->data;
	}
	function update($data, $doLastUpdate = true)
	{
		$table	= $this->table();
		$key	= $this->key();
		$id	= makeIDS($data['id']);
		unset($data['id']);

		reset($data);
		while(list($field, $value)=each($data))
		{
			if (is_string($value)){
				if (function_exists('makeSQLLongDate') && ($date = makeSQLLongDate($value)))
				{
					$data[$field] = $date;
					continue;
				}
				if ($date = makeDateStamp($value)){
					$data[$field]=makeSQLDate($date);
					continue;
				};
			}
			makeSQLValue($data[$field]);
		}
//		print_r($data); die;

		if ($doLastUpdate) $data['lastUpdate']=makeSQLDate(time());
		if ($id){
			$k = makeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else
			$id = $this->insertRow($table, $data);
//echo mysql_error();			
		return $id?$this->data[$key]=$id:0;
	}
	//	util functions
	function setValue($id, $field, $val, $doLastUpdate = true){
		$data = array('id'=>$id, $field=>$val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate = true){
		$data['id']=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, &$array, $bDelayed = false)
	{
		reset($array);
		$table = makeField($table);
		$fields=''; $comma=''; $values='';
		foreach($array as $field => $value)
		{
			$field	= makeField($field);
			$fields	.= "$comma$field";
			$values	.= "$comma$value";
			$comma	= ',';
		}
		
		if ($bDelayed) $res = $this->dbLink->dbExec("INSERT DELAYED INTO $table ($fields) VALUES ($values)", 0, 0);
		$res =  $this->dbLink->dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0);

		unset($table);
		unset($fields);
		unset($values);

		return $res;
	}
	function updateRow($table, &$array, $sql)
	{
		reset($array);
		$table = makeField($table);
		$command=''; $comma='SET ';
		while(list($field, $value)=each($array)){
			$field	=makeField($field);
			$command.="$comma$field=$value";
			$comma	= ',';
		}
		return $this->execSQL("UPDATE $table $command $sql");
	}
	function folder($id = 0){
		if (!$id) $id = $this->id();
		if ($id){
			$fields= $this->data['fields'];
			if (!is_array($fields)) $fields = unserialize($fields);
			$path	= $fields['filepath'];
			if ($path) return $this->images.'/'.$path;
		}
		$userID = function_exists('userID')?userID():0;
		return $this->images.'/'.($id?$id:"new$userID");
	}
	function url($id=0)		{ return $this->url.($id?$id:$this->id()); }
};

function makeIDS($id, $separator = ',')
{
	if (!is_array($id)) $id = explode($separator, $id);
	foreach($id as $ndx => &$val)
	{
		$val = trim($val);
		if (preg_match('#^\d+$#', $val)){
			$val = (int)$val;
		}else{
			if ($val) makeSQLValue($val);
		}
		if (!$val) unset($id[$ndx]);
	}
	return implode($separator, $id);
}

function makeDateStamp($val){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $val, $v)){
		list(,$d,$m,$y) = $v;
		return time(0, 0, 0, $m, $d, $y);
	}else
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return time($h, $i, 0, $m, $d, $y);
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return time($h, $i, $s, $m, $d, $y);
	}
	return 0;
}
function dateStamp($val){
	if (!$val) return;
	return date('d.m.Y H:i', $val);
}

?>
<? // Module module_mysql loaded from  _modules/_module_database/module_mysql.php ?>
<?
/*
//	Класс для манипуляции базой данных MySQL
function dbConfig(){
	//	Смотрим локальные настройки базы данных
	$ini		= getCacheValue('ini');
	$dbIni		= $ini[':db'];
	//	Если их нет, пробуем глобальные
	if (!is_array($dbIni)){
		//	Получим глобальные правила
		$globalDb	= $ini[':globalSiteDatabase'];
		if (!is_array($globalDb)){
			$ini		= getGlobalCacheValue('ini');
			//	Получим глобальные правила
			$globalDb	= $ini[':globalSiteDatabase'];
			if (!is_array($globalDb)) $globalDb = array();
		}
		//	Пройдемся по правилам
		foreach($globalDb as $rule => $dbKey){
			if (!preg_match("#$rule#i", $_SERVER['HTTP_HOST'])) continue;
			//	Если правило подходит, возмем значение из нового ключа
			$dbIni	= $ini[$dbKey];
			break;
		}
		//	Если настроек не найдено, пробуем стандартные
		if (!is_array($dbIni))
			$dbIni = $ini[':db'];
	}
	return $dbIni;
}
//	Open database
function dbConnect($bCreateDatabase = false)
{
	if (defined('dbConnect')) return $GLOBALS['dbConnection'];
	define('dbConnect', true);
	return dbConnectEx(dbConfig(), $bCreateDatabase);
}
function dbConnectEx($dbIni, $bCreateDatabase = false)
{
	$ini		= getGlobalCacheValue('ini');
	$pConnect	= $ini[':']['mySQLpconnect'];
	
	$dbhost	= $dbIni['host'];
	$dbuser	= $dbIni['login'];
	$dbpass	= $dbIni['passw'];
	$db		= $dbIni['db'];

	$timeStart	= getmicrotime();
	$cnn		= NULL;
	if ($pConnect)	$cnn = mysql_pconnect($dbhost, $dbuser, $dbpass);
	if (!$cnn)		$cnn = mysql_connect($dbhost, $dbuser, $dbpass);
	$GLOBALS['dbConnection'] = $cnn;
	
	$time 		= round(getmicrotime() - $timeStart, 4);
	if (!defined('restoreProcess')){
		module('message:sql:trace', "$time CONNECT to $dbhost");
		module('message:sql:error', mysql_error());
	}

	if (mysql_error()){
		module('message:sql:error', mysql_error());
		module('message:error', 'Ошибка открытия базы данных.');
		return;
	}
//	dbExec("SET character_set_results = 'cp1251'");
//	dbExec("SET character_set_client = 'cp1251'");
	if ($bCreateDatabase) dbExec("CREATE DATABASE `$db`");

	dbExec("SET NAMES UTF8");
	dbSelect($db, $GLOBALS['dbConnection']);

	return $GLOBALS['dbConnection'];
}
function dbTablePrefix()
{
	$dbConfig	= dbConfig();
	$prefix	= $dbConfig['prefix'];
	$url		= preg_replace('#[^\d\w]+#', '_', getSiteURL());
	if (!$prefix) return $url.'_';
	return "$url_$prefix".'_';
}
function dbTableName($name){
	$prefix = dbTablePrefix();
	return "$prefix$name";
};

function dbExec($sql, $rows=0, $from=0, &$dbLink = NULL){// echo $sql;
	if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";

	$timeStart	= getmicrotime();
	$res		= mysql_query($rows?"$sql LIMIT $from, $rows":$sql);
	$time 		= round(getmicrotime() - $timeStart, 4);

	if (!defined('restoreProcess')){
		module('message:sql:trace', "$time $sql");
		module('message:sql:error', mysql_error());
	}

	return $res;
}
function dbSelect($db, &$dbLink)	{ return mysql_select_db($db); }
function dbRows($id)				{ return mysql_num_rows($id);}
function dbResult($id)				{ return mysql_fetch_array($id, MYSQL_ASSOC);}
function dbRowTo($id, $row)			{ return mysql_data_seek($id, $row);}
function dbExecIns($sql, $rows = 0, &$dbLink){
	dbExec($sql, $rows, 0, $dbLink);
	return mysql_insert_id();
}
function dbExecQuery($sql, &$dbLink){ 
	$err= array();
	$q	= explode(";\r\n", $sql);
	while(list(,$sql)=each($q)){
		if (!$sql) continue;
		if (dbExec($sql, 0, 0, $dbLink)) continue;
		$e 		= mysql_error($dbLink);
		$err[] 	= $e;
	}
	return $err;
}
*/
//	Подготавливаются данные в соотвествии с правилами SQL
function makeSQLValue(&$val)
{
	switch(gettype($val)){
	case 'int': 	break;
	case 'float':
	case 'double':
		$val = str_replace(',', '.', $val);
	 	break;
	case 'NULL':
		$val = 'NULL';
		break;
	case 'array':
		$val = serialize($val);
	default:
		if (strncmp($val, 'FROM_UNIXTIME(', 14)==0) break;
		if (strncmp($val, 'DATE_ADD(', 9)==0) break;
		$db	= new dbRow();
		$val= $db->dbLink->escape_string($val);
		$val= "'$val'";
		break;
	}
}
function sqlDate($val)		{ return date('Y-m-d H:i:s', (int)$val); }
function makeSQLDate($val)	{ $val = sqlDate($val); return "DATE_ADD('$val', INTERVAL 0 DAY)"; }
function makeField($val)	{ return "`$val`"; }
function makeDate($val)
{
	// mysql date looks like "yyyy-mm-dd hh:mm:ss"
	$year	= (int)substr($val, 0, 4);
	$month	= (int)substr($val, 5, 2);
	$day	= (int)substr($val, 8, 2);
	$hour	= (int)substr($val, 11, 2);
	$min	= (int)substr($val, 14, 2);
	$sec	= (int)substr($val, 17, 2);
	if (!$year) return NULL;
	
	// Warning: mktime uses a strange order of arguments
	$d = mktime($hour, $min, $sec, $month, $day, $year);
	if ($d < 0) $d = NULL;
	return $d;
}
//	dd-mm-yy h:i:s
function makeSQLLongDate($dateStamp){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $dateStamp, $v)){
		list(,$d,$m,$y) = $v;
		return "DATE_ADD('$y-$m-$d', INTERVAL 0 SECOND)";
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $dateStamp, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return "DATE_ADD('$y-$m-$d $h:$i:0', INTERVAL 0 SECOND)";
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $dateStamp, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return "DATE_ADD('$y-$m-$d $h:$i:$s', INTERVAL 0 SECOND)";
	}
	return;
}
//	dd-mm-yy h:i:s
function makeLongDate($dateStamp, $bFullDate = false){
	// mysql date looks like "yyyy-mm-dd hh:mm:ss"
	$year	= (int)substr($dateStamp, 0, 4);
	$month	= (int)substr($dateStamp, 5, 2);
	$day	= (int)substr($dateStamp, 8, 2);
	$hour	= (int)substr($dateStamp, 11, 2);
	$min	= (int)substr($dateStamp, 14, 2);
	$sec	= (int)substr($dateStamp, 17, 2);
	if (!$year) return;
	return sprintf($bFullDate?"%02d.%02d.%04d %02d:%02d:%02d":"%02d.%02d.%04d", $day,$month,$year,$hour,$min,$sec);
}

function dbParseValue($name, $code)
{
	if (!preg_match("#$name\s*=\s*([^\s]+)#", $code, $var)) return NULL;
	return $var[1];
}
//	fields $fields[name]=array{'type'=>'int', 'length'=>'11'};.....
function dbAlterTable($table, $fields, $bUsePrefix = true, $dbEngine = '', $rowFormat = '')
{
	$dbLink	= new dbRow();
	$dbLink	= $dbLink->dbLink;
	$dbLink->dbConnect(true);

	if ($bUsePrefix) $table = $dbLink->dbTableName($table);

	if (!$dbEngine)	$dbEngine	= 'MyISAM';
	if (!$rowFormat)$rowFormat	= 'DYNAMIC';
	
//define('_debug_', true);

	$alter	= array();
	$rs		= $dbLink->dbExec("DESCRIBE `$table`");
	if ($rs)
	{
		$rs2	= $dbLink->dbExec("SHOW CREATE TABLE `$table`");
		$data	= $dbLink->dbResult($rs2);
		//	Database engine
		$thisEngine		= dbParseValue('ENGINE',	$data['Create Table']);
		if ($thisEngine != $dbEngine){
			$dbLink->dbExec("ALTER TABLE `$table` ENGINE=$dbEngine");;
		}
		//	Database row format
		$thisRowFormat	= dbParseValue('ROW_FORMAT',$data['Create Table']);
		if ($thisRowFormat != $rowFormat){
			$dbLink->dbExec("ALTER TABLE `$table` ROW_FORMAT=$rowFormat");;
		}
		//	Database keys and fields
		while($data = $dbLink->dbResult($rs))
		{
			$name	= $data['Field'];
			$f 	= $fields[$name];
			if (!$f) continue;
			
			$f['Field'] = $name;
			dbAlterCheckField($alter["CHANGE COLUMN `$name` `$name`"], $f, $data);
			unset($fields[$data['Field']]);
		}
		
		foreach($fields as $name => $f){
			$data 		= array();
			$f['Field'] = $name;
			dbAlterCheckField($alter["ADD COLUMN `$name`"], $f, $data);
		}
		
		$sql = array();
		foreach($alter as $name=>$value){
			if (!$value) continue;
			$value = implode(' ', $value);
			$sql[] = "$name $value";
		}
		if ($sql){
			$sql = implode(', ', $sql);
//			echo("ALTER TABLE $table $sql");
			$dbLink->dbExec("ALTER TABLE $table $sql");
			module('message:sql', "Updated table `$table`");
//			echo mysql_error();
		}
		$dbLink->dbExec("OPTIMIZE TABLE $table");
		return;
	}
	//	Create Table
	foreach($fields as $name => $f){
		$data 		= array();
		$f['Field'] = $name;
		dbAlterCheckField($alter["`$name`"], $f, $data, true);
	}
	$sql = array();
	foreach($alter as $name=>$value){
		if (!$value) continue;
		$value = implode(' ', $value);
		$sql[] = "$name $value";
	}
	if (!$sql) return;
	$sql = implode(', ', $sql);
	//	CREATE TABLE `1` (  `1` INT(10) NULL ) COLLATE='cp1251_general_ci' ENGINE=InnoDB ROW_FORMAT=DEFAULT;
	$dbLink->dbExec("CREATE TABLE $table ($sql) COLLATE='utf8_general_ci' ENGINE=$dbEngine ROW_FORMAT=$rowFormat;");
	module('message:sql', "Created table `$table`");
}
function dbAlterCheckField(&$alter, &$need, &$now, $bCreate = false)
{	
	if (!isset($need['Type']))	$need['Type']	= $now['Type'];
	if (!isset($need['Null']))	$need['Null']	= 'YES';
	if (!isset($need['Key']))	$need['Key']	= '';
	if (!isset($need['Extra']))	$need['Extra']	= '';
	if (!isset($need['Default']))$need['Default']=NULL;

	$bChanged = false;
	
//	print_r($now);
//	print_r($need);
	
	$bChanged |= $need['Type'] != $now['Type'];
	$bChanged |= isset($need['Null']) 	&& $need['Null'] 		!= $now['Null'];
	$bChanged |= isset($need['Default'])&& $need['Default'] 	!= $now['Default'];
	$bChanged |= isset($need['Key'])	&& $need['Key'] 		!= $now['Key'];
	
	if (!$bChanged) return;

	$alter[] = $need['Type'];

	$n = $need['Null'];
	$alter[] = $n=='NO'?'NOT NULL':'NULL';
	
	$n = $need['Default'];
	if ($n != NULL){
		if ($n == '(NULL)') $n = 'NULL';
		else
		if ($n == '') 		$n = "''";
		else
		$n = "'$n'";
		$alter[] = "DEFAULT $n";
	}
	
	$n = $need['Extra'];
	if ($n != NULL){
		$alter[] = "$n";
	}
	
	$n = $need['Key'];
	if ($n != $now['Key']){
		$ndxName = $need['Field'];

		if ($n){
			if ($n == 'PRI') $n = 'PRIMARY KEY';
			else
			if ($n == 'UNI') $n = 'UNIQUE INDEX';
			else{
				if ($need['Type'] == 'text') $n = 'FULLTEXT INDEX';
				else $n = 'INDEX';
			}
		}
		
		if ($bCreate){
			if ($n) $alter[] = ", $n `$ndxName` (`$ndxName`)";
		}else{
//			if ($now['Key'])$alter[] = ", DROP INDEX `$ndxName`";
			if ($n)		 	$alter[] = ", ADD $n `$ndxName` (`$ndxName`)";
		}
	}
}
?>
<? // Module module_bask loaded from  _packages/_shop/_module.bask/module_bask.php ?>
<?
function module_bask($fn, $data)
{
	if (!defined('bask')){
		define('bask', true);
		
		$bask	= array();
		@$b		= explode(';', $_COOKIE['bask']);
		foreach($b as $row){
			$row	= explode('=', $row);;
			@$id	= (int)$row[0];
			@$count	= (int)$row[1];
			if ($id && $count >= 0)
				$bask[$id] = $count;
		}
		$GLOBALS['_CONFIG']['bask'] = $bask;
	}
	if (!$fn) return $GLOBALS['_CONFIG']['bask'];

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("bask_$fn");
	return $fn?$fn($GLOBALS['_CONFIG']['bask'], $val, $data):NULL;
}

function bask_count($bask, $val, $data)
{
	$count = 0;
	foreach($bask as $c) $count += $c;
	echo $count;
}

function bask_button($bask, $id, $data){
	m('page:style', 'bask.css');
	m('script:ajaxLink');
	$url	= getURL("bask_add$id");
	if ($data){
		$action = (is_array($data))?implode('', $data):$data;
	}else{
		$action	= @$bask[$id]?'Добавить +1':'Купить';
	}
	echo "<a href=\"$url\" id=\"ajax\" class=\"baskButton\">$action</a>";
}

function setBaskCookie(&$bask)
{
	noCache();
	$val = array();
	foreach($bask as $id => $count){
		if ($id < 1 || $count < 0){
			unset($bask[$id]);
			continue;
		}
		$val[] = "$id=$count";
	}
	
	$GLOBALS['_CONFIG']['bask'] = $bask;
	cookieSet('bask', implode(';', $val));
}

function bask_update($bask, $val, $data)
{
	@$id = $data[1];
	switch($val){
	case 'set':
		$bask[$id] = 1;
		module('message', 'Товар добавлен');
		break;
	case 'add':
		@$bask[$id] += 1;
		module('message', 'Товар добавлен');
		break;
	case 'delete':
		$bask[$id] = -1;
		module('message', 'Товар удален');
		break;
	case 'clear':
		$bask = array();
		module('message', 'Корзина очищена');
		break;
	}
	
	setBaskCookie($bask);
	module('order:order');
}
?>
<? // Module module_order loaded from  _packages/_shop/_module.order/module_order.php ?>
<?
function module_order($fn, &$data){
	//	База данных 
	$db 		= new dbRow('order_tbl', 'order_id');
	$db->url 	= 'order';
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("order_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}
function makeOrderSearchField(&$orderData)
{
	$search = array();
	foreach($orderData as $type => $val){
		$search[] = implode(' ', $val);
	}
	return implode(' ', $search);
}
?>

