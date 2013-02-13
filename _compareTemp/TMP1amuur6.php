<? // Module module_admin loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.admin/module_admin.php ?>
<?
function module_admin(&$fn, &$data)
{
	if (!access('write', '')) return;

	noCache();

	module('script:jq_ui');
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("admin_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function startDrop($search, $template = ''){
	if (!$search || testValue('ajax')) return;
	$rel = makeQueryString($search, 'data');
	echo "<div class=\"droppable\" rel=\"$rel&template=$template\">";
}
function endDrop($search){
	if (!$search || testValue('ajax')) return;
	echo "</div>";
}
function module_admin_cache($val, $data)
{
	if (!access('clearCache', '')) return;

	if (testValue('clearCache'))
	{
		clearCache();
		module('doc:recompile');
	}else
	if (testValue('recompileDocuments')){
		module('doc:recompile');
		module('message', 'Документы скомпилированы');
	}
}
?>
<? // Module module_doc loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/module_doc.php ?>
<?
function module_doc($fn, &$data)
{
	//	База данных пользователей
	$db 		= new dbRow('documents_tbl', 'doc_id');
	$db->sql	= 'deleted = 0';
	$db->images = images.'/doc';
	$db->url 	= 'page';
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("doc_$fn");
	return $fn?$fn($db, $val, $data):NULL;
}

function currentPage($id = NULL){
	if ($id != NULL) $GLOBALS['_SETTINGS']['page']['currentPage'] = $id;
	else return @$GLOBALS['_SETTINGS']['page']['currentPage'];
}

function docPrice(&$data, $name = ''){
	if ($name == '') $name = 'base';
	@$price	= $data['fields'];
	@$price	= $price['price'];
	@$price = (float)$price[$name];
	return $price;
}
function priceNumber($price){
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = ''){
	$price = docPrice(&$data, $name);
	if (!$price) return;
	
	$price = priceNumber($price);
	return "<span class=\"price\">$price</span>";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat(&$data, $name);
	if ($price) $price = "<span class=\"priceName\">Цена: $price руб.</span>";
	return $price;
}
function docType($type, $n = 0)
{
	$docTypes	= getCacheValue('docTypes');
	$names		= explode(':',  $docTypes[$type]);
	return @$names[$n];
}
function docTitle($id){
	$db		= module('doc');
	$folder	= $db->folder($id);
	@list($name, $path) = each(getFiles("$folder/Title"));
	return $path;
}
function compilePrice(&$data, $bUpdate = true)
{
	$db	= module('doc', $data);
	$id	= $db->id();
	
	if ($price = docPrice($data))
	{
		$docPrice	= getCacheValue('docPrice');
		foreach($docPrice as $maxPrice => $name){
			if ($price >= $maxPrice) continue;
			$data[':property']['Цена'] = $name;
			break;
		}
		if ($price >= $maxPrice){
			$data[':property']['Цена'] = "> $maxPrice";
		}
	}else{
			$data[':property']['Цена'] = '';
	}
	if ($bUpdate)
		module("prop:set:$id", $data[':property']);
}
function document(&$data){
	if (!beginCompile(&$data, 'document')) return;
	echo $data['originalDocument'];
	endCompile(&$data, 'document');
}
//	Начало кеширования компилированной версии 
function beginCompile(&$data, $renderName)
{
	
	$rendered = &$data['document'];
	if (!is_array($rendered)){
		@$rendered = unserialize($rendered);
		if (!is_array($rendered)) $rendered = array();
	}

	@$compiled = $rendered[$renderName];
	if (isset($compiled) && localCacheExists()){
		echo $compiled;
		return false;
	}

	ob_start();
	return true;
}
//	Конец кеширования компилированной версии 
function endCompile(&$data, $renderName)
{
	$document	= ob_get_clean();
	event('document.compile', &$document);
	echo $document;
	if (!localCacheExists()) return;

	$db			= module('doc:', $data);
	$id			= $db->id();
	if (!$id){
		module('message:trace:error', "Document not compiled, $renderName");
		return;
	}
	module('message:trace', "Document compiled, $id => $renderName");
	$data['document'][$renderName] = $document;
	
	//	Сохранить данные
	$db->setValue($id, 'document', $data['document'], false);
}
function doc_recompile($db, $id, $data){
	$ids = makeIDS($ids);
	if ($ids)
	{
		$db->open("`doc_type` = 'product' AND `doc_id` IN ($ids)");
		while($data = $db->next()){
			compilePrice(&$data);
		}
		
		$ids = explode(',', $ids);
		foreach($ids as $id){
			if ($id) clearThumb($db->folder($id));
		}
		
		$db->setValue($ids, 'document', NULL, false);
	}else{
		$db->open("`doc_type` = 'product'");
		while($data = $db->next()){
			compilePrice(&$data);
		}
		
		$a = array();
		setCacheValue('textBlocks', $a);
		clearThumb(images);
		
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = NULL");
	}
}
?>
<? // Module module_doc_access loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/module_doc_access.php ?>
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
			return hasAccessRole('admin,developer,writer,manager');
		case 'delete':
			return hasAccessRole('admin,developer,writer');
	}
}

function module_doc_add_access($mode, $data)
{
	if ($mode != 'add') return false;
	
	@$baseType	= $data[1];
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
		case 'product:comment';
		return hasAccessRole('admin,developer,writer,manager,user');
	}
	return false;
}

?>
<? // Module module_doc_page loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/module_doc_page.php ?>
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

<? // Module module_doc_read loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/module_doc_read.php ?>
<?
function doc_read(&$db, $template, &$search)
{
	$sql = array();
	doc_sql($sql, $search);
	if (!$sql) return;

	$db->open($sql);
	$fn = getFn("doc_read_$template");
	if (!$fn) $fn = getFn('doc_read_default');

	ob_start();
	$search = $fn?$fn($db, $search, &$data):NULL;
	$p = ob_get_clean();
	if (is_array($search) && hasScriptUser('draggable')){
		startDrop($search, $template);
		echo $p;
		endDrop($search, $template);
	}else{
		echo $p;
	}
}
?>

<? // Module module_doc_sql loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/module_doc_sql.php ?>
<?
function doc_sql(&$sql, $search)
{
	$path = array();
	///////////////////////////////////////////
	//	Найти по номеру документа
	if (isset($search['id']))
	{
		$val	= $search['id'];
		$val	= makeIDS($val);
		if ($val) $sql[]	= "`doc_id` IN ($val)";
		else $sql[] = 'true = false';
	}

	if (@$val = $search['title'])
	{
		$val	= mysql_real_escape_string($val);
		$sql[]	= "`title` LIKE ('%$val%')";
	}

	///////////////////////////////////////////
	//	Найти по типу документа
	if ($val = @$search['type'])
	{
		$val	= makeIDS($val);
		$sql[]	= "`doc_type` IN ($val)";
	}
	
	prop_sql(&$sql,	&$search);
	price_sql(&$sql,&$search);

	return $path;
}
?>
<? // Module module_doc_menu loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_edit/module_doc_menu.php ?>
<?
function doc_menu($id, &$data, $bSimple = false){
	$menu = array();

	if (!$bSimple && access('add', "doc:$data[doc_type]:article"))
		$menu['Добавть документ#ajax_edit']	= getURL("page_add_$id", 'type=article');

	if (!$bSimple && access('add', "doc:$data[doc_type]:page"))
		$menu['Добавть раздел#ajax_edit']	= getURL("page_add_$id", 'type=page');

	if (!$bSimple && access('add', "doc:$data[doc_type]:product"))
		$menu['Добавть товар#ajax_edit']	= getURL("page_add_$id", 'type=product');

	if (!$bSimple && access('add', "doc:$data[doc_type]:catalog"))
		$menu['Добавть каталог#ajax_edit']	= getURL("page_add_$id", 'type=catalog');

	if (access('write', "doc:$id"))
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id");

	if (!$bSimple && access('delete', "doc:$id"))
		$menu['Удалить#ajax']	= getURL("page_edit_$id", 'delete');
		
	if ($menu){
		$menu[':draggable'] = "doc-page_edit_$id-$data[doc_type]";
	}

	return $menu;
}
?>
<? // Module module_bask loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.bask/module_bask.php ?>
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
			if ($id && $count > 0)
				$bask[$id] = $count;
		}
		$GLOBALS['_CONFIG']['bask'] = $bask;
	}
	if (!$fn) return $GLOBALS['_CONFIG']['bask'];

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("bask_$fn");
	return $fn?$fn($GLOBALS['_CONFIG']['bask'], $val, $data):NULL;
}

function bask_button($bask, $id){
	$url = getURL("bask_add$id");
	$action = @$bask[$id]?'Добавить +1':'Купить';
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
	module('bask:full');
}
?>
<? // Module module_bask_compact loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.bask/module_bask_compact.php ?>
<?
function bask_compact($bask, $val, &$data)
{
	if ($bask){
		$s			= array();
		$s['type']	= 'product';
		$s['id']	= array_keys($bask);
		
		$cont	= 0;
		$sql	= array();
		doc_sql(&$sql, $s);
		
		$db = module('doc');
		$db->open($sql);
		while($data	= $db->next()){
			$count += $bask[$db->id()];
		}
	}else{
		$count = 0;
	}
	
	if ($count) $ordered = "В корзине <b>$count</b> шт.";
	else $ordered = "В корзине пусто";

	module('script:ajaxLink');
	module('page:style', 'bask.css');
?>
<div class="bask compact">
<div class="baskTitle"><a href="<?= module('getURL:bask')?>" id="ajax">Корзина:</a></div>
<div class="baskAvalible"><?= $ordered?></div>
</div>
<? } ?>
<? // Module module_order loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.order/module_order.php ?>
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
	return $fn?$fn($db, $val, &$data):NULL;
}
function orderSearchField($order)
{
	$search		= array();
	$search[]	= $order['orderData']['name'];
	if (@$order['orderData']['phone'])	$search[] = $order['orderData']['phone'];
	if (@$order['orderData']['email'])	$search[] = $order['orderData']['email'];
	return implode(' ', $search);
}
?>
<? // Module module_price loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.price/module_price.php ?>
<?
function module_price($fn, &$data){
	//	База данных пользователей
	$db = new dbRow('price_tbl', 'price_id');
	if (!$fn){
		if (is_array($data)) $db->data = $data;
		return $db;
	}
}
?>
<? // Module module_price_sql loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.price/module_price_sql.php ?>
<?
function price_sql(&$sql, $search)
{
	///////////////////////////////////////////
	//	Найти по цене
	if (@$val = $search['price'])
	{
		if ($val){
			$where		= '';
			$val		= explode('-', $val);
			@list($priceFrom, $priceTo) = $val;
			$priceFrom	= (float)trim($priceFrom);
			$priceTo	= (float)trim($priceTo);
			
			if ($priceFrom && $priceTo){
				$where = "price BETWEEN $priceFrom AND $priceTo";
			}else
			if ($priceFrom){
				if (count($val) > 1) $where = "price >= $priceFrom";
				else  $where = "price = $priceFrom";
			}else
			if ($priceTo){
				$where = "price <= priceTo";
			}

			if ($where){
				$db		= module('price');
				$table	= $db->table();
				$sql[]	= "`doc_id` IN (SELECT `doc_id` FROM $table WHERE $where)";
			}
		}
	}
}
?>
<? // Module module_prop loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.property/module_prop.php ?>
<?
function module_prop($fn, &$data)
{
	//	База данных пользователей
	$db	= new dbRow('prop_name_tbl', 'prop_id');
	$db->dbValue = new dbRow('prop_value_tbl', 'value_id');

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
	
	$sql	= array();
	$res	= array();
	$prop	= array();
	
	if ($group){
		$group	= explode(',', $group);
		foreach($group as &$val) makeSQLValue($val);
		$group	= implode(',', $group);
		$sql[]	= "`group` IN ($group)";
	}
	
	if ($docID){
		$docID	= makeIDS($docID);
		$ids	= array();

		$db->dbValue->open("doc_id IN ($docID)");
		while($data = $db->dbValue->next())
		{
			$ids[$data['prop_id']] = $data['prop_id'];
			$prop[$data['prop_id']][$db->dbValue->id()] = $data;
		}
		$ids	= implode(',', $ids);
		$sql[]	= "`prop_id` IN ($ids)";
	}

	$db->order = 'sort, name';
	$db->open($sql);
	while($data = $db->next())
	{
		$p	= array();
		if (@$propData = $prop[$db->id()])
		{
			$valueType	= $data['valueType'];
			foreach($propData as $iid => &$val) $p[$val[$valueType]] = $val[$valueType];
		}
		$data['property'] 	= implode(', ', $p);
		$res[$data['name']]	= $data;
	}

	return $res;
}
function prop_set($db, $docID, $data)
{
	if ($docID){
		$docID	= makeIDS($docID);
		$ids	= $docID;
		$docID	= explode(',', $docID);
	}
	
	if (!is_array($data)) return;
	$a = array();
	setCacheValue('propNames', $a);
	
	$valueTable	= $db->dbValue->table();
	foreach($data as $name => $prop)
	{
		$valueType	= 'valueText';		
		$iid		= module("prop:add:$name", &$valueType);//prop_add($db, $name, &$valueType, $group);
		if (!$iid || !$docID) continue;

		$db->dbValue->exec("DELETE FROM $valueTable WHERE `prop_id` = $iid AND `doc_id` IN ($ids)");
		$prop	= explode(', ', $prop);
		foreach($prop as $val)
		{
			$val = trim($val);
			if (!$val) continue;
			
			$d				= array();
			$d['prop_id']	= $iid;
			$d[$valueType]	= $val;
			
			foreach($docID as $doc_id)
			{
				$d['doc_id'] = $doc_id;
				$db->dbValue->update($d, false);
			}
		}
	}
}

function prop_delete($db, $docID, $dtaa){
	$db->dbValue->deleteByKey('doc_id', $docID);
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
		$iid = $db->id();
		$valueType = $data['valueType'];
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
?>
<? // Module module_prop_read loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.property/module_prop_read.php ?>
<?
function prop_read($db, $val, $data)
{
	$prop = module("prop:get:$data[id]:$data[group]");
	if (!$prop) return;

	echo '<ul>';
	foreach($prop as $name => $data)
	{
		if ($name[0] == ':' || $name[0] == '!') continue;
		
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
	echo '</ul>';
}
?>
<? // Module module_prop_sql loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.doc/_module.property/module_prop_sql.php ?>
<?
function prop_sql(&$sql, &$search)
{
	//	Найти по родителю
	if (@$val = $search['parent'])
		$search['prop'][':parent'] = $val;

	//	Найти по свойствам
	if (@$val = $search['prop'])
	{
		$bHasPropSQL = false;
		$propNames	= array_keys($val);
		foreach($propNames as &$propName) makeSQLValue($propName);
		$propNames	= implode(',', $propNames);
		
		$md5Val		= hashData($val);
		$propCache	= getCacheValue('propNames');
		$thisSQL 	= &$propCache[$md5Val];
		
		if (!$thisSQL){
			$thisSQL	= array();
			$db			= module('prop');
			$db->open("`name` IN ($propNames)");
			while($data = $db->next())
			{
				$id		= $db->id();
				@$values= $val[$data['name']];
				if (!$values) continue;
				
				$values		= explode(', ', $values);
				$valuesCount= count($values);
				
				if ($data['valueType'] == 'valueDigit'){
					if ($valuesCount > 1){
						foreach($values as &$value) $value = (int)$value;
						$values	= implode(',', $values);
						$s		= "`prop_id` = $id AND `$data[valueType]` IN ($values)";
					}else{
						$value = (int)$values[0];
						$s		= "`prop_id` = $id AND `$data[valueType]` = $value";
					}
				}else{
					if ($valuesCount > 1){
						foreach($values as &$value) makeSQLValue($value);
						$values	= implode(',', $values);
						$s		= "`prop_id` = $id AND `$data[valueType]` IN ($values)";
					}else{
						$value = $values[0];
						makeSQLValue($value);
						$s		= "`prop_id` = $id AND `$data[valueType]` = $value";
					}
				}
	
				$db->dbValue->fields = 'doc_id';
				$s 			= $db->dbValue->makeSQL($s);
				$thisSQL[]	= "`doc_id` IN ($s)";
			}
			if (!$thisSQL) $thisSQL[] = 'true = false';
			
			$thisSQL = implode(' AND ', $thisSQL);
			setCacheValue('propNames', $propCache);
		}
		$sql[] = $thisSQL;
	}
}
?>
<? // Module module_file loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.file/module_file.php ?>
<?
function module_file($val, $data=''){
	//	Попробовать загрузить дополнительный модуль
	@list($val, $v)=explode(':', $val, 2);
	$fn = getFn("file_$val");
	if ($fn) return $fn($v, $data);
}
?>
<? // Module module_gallery loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.gallery/module_gallery.php ?>
<?
function module_gallery($fn, &$data){
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("gallery_$fn");
	return $fn?$fn($val, $data):NULL;
}
?>
<? // Module module_links loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.links/module_links.php ?>
<?
function module_links($fn, &$url){
	$db		= new dbRow('links_tbl', 'link');
	if (!$fn) return $db;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("links_$fn");
	return $fn?$fn($db, $val, &$url):NULL;
}

function links_url(&$db, $val, $url)
{
	$nativeLink	= getCacheValue('nativeLink');
	$u			= strtolower($url);
	@$nativeURL	= &$nativeLink[$u];
	if ($nativeURL){
		echo renderURLbase($nativeURL);
		return;
	}
	
	makeSQLValue($u);
	$db->open("link = $u");
	$data = $db->next();
	if ($data){
		$nativeURL = $data['nativeURL'];
		echo renderURLbase($nativeURL);
	}
	setCacheValue('nativeLink', $nativeLink);
}
function links_prepareURL(&$db, $val, &$url)
{
	$links	= getCacheValue('links');
	@$u		= $links[$url];
	if (is_string($u)){
		if ($u) $url = $u;
		return;
	}

	$u		= $url;
	makeSQLValue($u);
	$db->open("nativeURL = $u");
	
	if ($data = $db->next()){
		$links[$url] = $data['link'];
	}else{
		$links[$url] = '';
	}

	setCacheValue('links', $links);
}
?>
<? // Module module_page loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.page/module_page.php ?>
<?
function module_page($fn, &$data)
{
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("page_$fn");
	return $fn?$fn($val, &$data):NULL;
}
function module_display($val, &$data){
	return page_display($val, &$data);
}

function page_header(){
?><title><? module("page:title:siteTitle") ?></title>
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
		echo htmlspecialchars($title);
		return $title;
	}
}

function page_meta($val, $data)
{
	@$store = &$GLOBALS['_CONFIG']['page']['meta'];
	if (!is_array($store)) $store = array();

	if (!$val){
		foreach($store as $name => $val) page_meta($name, NULL);
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
		foreach($store as $style){
			$style = htmlspecialchars($style);
			echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$style\"/>\r\n";
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
		foreach($store as $script){
			echo $script, "\r\n";
		}
	}
}

?>
<? // Module module_read loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.page/module_read.php ?>
<?
function module_read($name, $data)
{
	$cache			= getCacheValue('textBlocks');
	$textBlockName	= "$name.html";
	if (!isset($cache[$textBlockName]))
	{
		$val = @file_get_contents(images."/$textBlockName");
		event('document.compile', &$val);
		$cache[$textBlockName] = $val;
		setCacheValue('textBlocks', $cache);
	}
	
	$menu = array();
	if (access('write', "text:$name")){
		$menu['Изменить#ajax_edit']	= getURL("read_edit_$name");
		$menu['Удалить#ajax']		= getURL("read_edit_$name", 'delete');
	};
	
	beginAdmin();
	echo $cache[$textBlockName];
	endAdmin($menu, $data?false:true);
}

function module_read_access($mode, $data)
{
	switch($mode){
		case 'read': return true;
	}
	return hasAccessRole('admin,developer,writer');
}
?>

<? // Module module_script loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.script/module_script.php ?>
<?
function module_script($val)
{
	$GLOBALS['_SETTINGS']['script'][$val] = true;
	$fn = getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	module("page:script:$val", ob_get_clean());
}
function hasScriptUser($val){
	return @$GLOBALS['_SETTINGS']['script'][$val];
}
?>
<?
function script_jq($val){
	$ver = getCacheValue('jQueryVersion');
?>
<? if (testValue('ahax')){ ?>
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
<? if (testValue('ahax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery.ui == 'undefined') {
	 document.write('<' + 'script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<script type="text/javascript" src="script/<?= $ver?>/js/<?= $ver?>.min.js"></script>
<? } ?>

<? function script_jq_print($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.printElement.min.js"></script>
<? } ?>

<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cookie.min.js"></script>
<? } ?>

<?
function script_overlay($val){
	module('script:jq');
?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
(function( $ ) {
  $.fn.overlay = function(closeFn) {
		// Create overlay and append to body:
		$("#fadeOverlayLayer").remove();
		$("#fadeOverlayHolder").remove();
		var overlay = $('<div id="fadeOverlayLayer" />')
			.appendTo('body')
			.css({
				'position': 'fixed',
				'top': 0, 'left': 0, 'right': 0, 'bottom': 0,
				'opacity': 0.8,
				'background': 'black'
				})
			.click(function(){
				$("#fadeOverlayLayer").remove();
				$("#fadeOverlayHolder").remove();
			});

		return $('<div id="fadeOverlayHolder" />').appendTo('body').append(this);
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
<script type="text/javascript" language="javascript">
$(function(){
	$('[id*="calendar"]').datepicker({
		dateFormat: 	'dd.mm.yy',
		monthNames: 	['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort:['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сент','Окт','Ноя','Дек'],
		dayNamesMin: 	['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'],
		firstDay: 		1});
});
</script>
<? } ?>

<? function script_lightbox($val){ module('script:jq'); ?>
<link rel="stylesheet" type="text/css" href="script/lightbox/css/jquery.lightbox-0.5.css"/>
<script type="text/javascript" src="script/lightbox/jquery.lightbox-0.5.js"></script>
<script type="text/javascript">
$(function(){
	$("a[rel='lightbox']").lightBox().removeAttr("rel");
});
</script>
<? } ?>

<? function script_CrossSlide($val){ module('script:jq'); ?>
<script type="text/javascript" src="script/jquery.cross-slide.min.js"></script>
<? } ?>

<? function script_menu($val){ module('script:jq'); ?>
<script type="text/javascript">
//	menu
$(function() {
	$('.menu.popup > li').hover(function(){
		$(".menu.popup ul").hide();
		$(this).find("ul").show();
	}, function(){
		$(".menu.popup ul").hide();
	});
});
</script>
<? } ?>

<? function script_ajaxLink($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
$(function(){
/*<![CDATA[*/
	$('a[id*="ajax"]').click(function()
	{
		var id = $(this).attr('id');
		$('<div />').overlay()
			.css({position:'absolute', top:0, left:0, right:0})
			.load($(this).attr('href'), 'ajax=' + id);
		return false;
	});
 /*]]>*/
});
</script>
<? } ?>
<? function script_ajaxForm($val){ module('script:overlay'); ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	//	Отправка через AJAX, только если есть overlay
	$(".ajaxForm").submit(function(){
		if ($('#fadeOverlayHolder').length == 0) return true;
		return submitAjaxForm($(this));
	}).removeClass("ajaxForm").addClass("ajaxSubmit");
	
	$(".ajaxFormNow").submit(function(){
		return submitAjaxForm($(this));
	}).removeClass("ajaxForm").addClass("ajaxSubmit");
});

function submitAjaxForm(form)
{
	if (form.hasClass('submitPending')) return;
	form.addClass('submitPending');
	
	$('#formReadMessage').remove();
	$('<div id="formReadMessage" class="message work">')
		.insertBefore(form)
		.html("Обработка данных сервером, ждите.");

	var ajaxForm = form.hasClass('ajaxSubmit')?'ajax_message':'';
	if (form.hasClass('ajaxReload')) ajaxForm = 'ajax';

	var formData = form.serialize();
	if (ajaxForm) formData += "&ajax=" + ajaxForm;

	$.post(form.attr("action"), formData)
		.success(function(data){
			form.removeClass('submitPending');
			if (form.hasClass('ajaxReload')){
				$('#fadeOverlayHolder').html(data);
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




<? // Module module_script_ajax loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.script/module_script_ajax.php ?>
<?
function module_script_ajax($val, &$config)
{
	if (testValue('ajax')){
		$ajaxTemplate = getValue('ajax');
		$config['page']['template'] = $ajaxTemplate?"page.$ajaxTemplate":'page.ajax';
	}
}
?>
<? // Module module_user loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.user/module_user.php ?>
<?
module('user:enter');
//	module user
function module_user($fn, &$data)
{
	//	База данных пользователей
	$db 		= new dbRow('users_tbl', 'user_id');
	$db->sql	= 'deleted = 0';
	$db->images = images.'user';
	$db->url 	= 'user';
	if (!$fn) return $db;
	
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("user_$fn");
	return $fn?$fn(&$db, $val, $data):NULL;
}

function module_user_access(&$val, &$data)
{
	list($mode,) = explode(':', $val);
	switch($mode){
		case 'read':	return true;
	}
	return hasAccessRole('admin,developer,writer,manager,accountManager');
}
function hasAccessRole($checkRole)
{
	@$userRoles	= $GLOBALS['_CONFIG']['user']['userRoles'];
	$roles 		= explode(',', $checkRole);
	
	return @array_intersect($userRoles, $roles);
}
?>
<? // Module module_user_enter loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module.user/module_user_enter.php ?>
<?
function user_enter($db, $val, &$data)
{
	if (testValue('logout')) user_logout();

	$login = $data?$data:getValue('login');
	//	Проверить регистрацию, если введен логин пользователя
	if (isset($login['login'])){	//	Если пользователь регистрируется
		@$md5 = getMD5($login['login'], $login['passw']);
		makeSQLValue($md5);
		$db->open("`md5` = $md5 AND `deleted` = 0 AND `visible` = 1");
		//	Проверка что такой пользователь есть
		//	Если пользователь найден, то регистрация
		if ($db->next()){
			define('firstEnter', true);
			return setUserData($db);
		}
		if ($val) return false;

		user_logout();
		module('message:error', 'Неверный логин или пароль');
		return false;
	}
	
	$md5 = @$_COOKIE['userSession5'];
	if ($md5){	//	Если пользователь в сессии, то ищем его в базе
		makeSQLValue($md5);
		$db->open("`md5` = $md5 AND `deleted` = 0 AND `visible` = 1");
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
		$db->open("`md5` = $md5 AND `deleted` = 0 AND `visible` = 1");
		//	Проверка что такой пользователь есть
		if ($db->next()){
			//	Если хешь совпадает, то регистрируем пользователя
			return setUserData($db);
		}
	}
	//	Сбрасываем авторегистрацию
	if ($val) return false;
	user_logout();

	return false;
}

function user_logout()
{
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
	if (@$login['remember'] || $remember){
		cookieSet('autologin5', $data['md5']);
	}else cookieSet('userSession5', $data['md5'], false);
	
	//	Сохранить данные текущего пользователя
	define('userID', $userID);
	$GLOBALS['_CONFIG']['user']['data']		= $data;
	$GLOBALS['_CONFIG']['user']['userRoles']= explode(',', $data['access']);

//	module('message:user:trace', "User '$data[login]' entered in site");
	return true;
}

function userID(){
	@$id = $GLOBALS['_CONFIG']['user']['data']['user_id'];
	return $id;
}

function getMD5($login, $passw){
	$login = strtolower($login);
	return md5("$login:$passw");
}

?>
<? // Module module_common loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/module_common.php ?>
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
?>
<? // Module module_cookie loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/module_cookie.php ?>
<?
function cookieSet($name, $val, $bStore = true)
{
	$time = $val && $bStore?time() + 3*7*24*3600:0;
	$_COOKIE[$name] = $val;
	setcookie($name, $val, $time, '');
}
?>

<? // Module module_getURL loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/module_getURL.php ?>
<?
function module_getURL($url, &$options){
	echo getURL($url, $options);
}
//	Получить правильную ссылку из пути.
function getURL($url = '', $options = '')
{
	$v		= $url?"/$url.htm":'/';
	event('site.prepareURL', &$v);
	$options= is_array($options)?makeQueryString($options):$options;
	return globalRootURL.($options?"$v?$options":$v);
}
?>
<? // Module module_message loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/module_message.php ?>
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
		return module('page:display:message', "<p class=\"$messageClass shadow\">$data</p>");
	}
	
	if (is_array($data)){
		ob_start();
		print_r($data);
		$data = ob_get_clean();
	}
	
	$data = rtrim($data);
	if (!$data) return;
	
	$class = strpos($val, 'error')?' class="errorMessage"':'';
	module('page:display:log', "<span$class>$val: <span>$data</span></span>\r\n");
}
?>
<? // Module module_page_compile loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/_compile/module_page_compile.php ?>
<?
function module_page_compile($val, &$thisPage){
	$GLOBALS['_CONFIG']['page']['compile']		= array();
	$GLOBALS['_CONFIG']['page']['compileLoaded']= array();

	//	<img src="" ... />
	//	Related path, like .href="../../_template/style.css"
	$thisPage	= preg_replace('#((href|src)\s*=\s*["\'])([^"\']+_[^\'"/]+/)#i', '\\1', 	$thisPage);
	//	{{moduleName=values}}
	$thisPage	= preg_replace_callback('#{{([^}]+)}}#', parsePageFn, 	$thisPage);
	//	{$variable} htmlspecialchars out variable
	$thisPage	= preg_replace_callback('#{(\$[^}]+)}#', parsePageValFn, $thisPage);
	//	{!$variable} direct out variable
	$thisPage	= preg_replace_callback('#{!(\$[^}]+)}#',parsePageValDirectFn, $thisPage);
	//	{beginAdmin}  {endAdmin}
	$thisPage	= str_replace('{beginAdmin}',	'<? beginAdmin() ?>',		$thisPage);
	$thisPage	= str_replace('{endAdmin}',		'<? endAdmin($menu) ?>',	$thisPage);
	$thisPage	= str_replace('{endAdminTop}',	'<? endAdmin($menu, true) ?>',$thisPage);
	//	<link rel="stylesheet" ... /> => use CSS module
	$thisPage	= preg_replace_callback('#<link\s+rel\s*=\s*[\'"]stylesheet[\'"][^>]*href\s*=\s*[\'"]([^>\'"]+)[\'"][^>]*/>#',parsePageCSS, $thisPage);
	//	{beginCompile:compileName}  {endCompile:compileName}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#', '<?  if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile:([^}]+)}#', '<?  endCompile(\$data, "\\1"); } ?>', $thisPage);
	$thisPage	= str_replace('{document}',	'<? document($data) ?>',$thisPage);

	$thisPage	= $thisPage.implode('', array_reverse($GLOBALS['_CONFIG']['page']['compileLoaded']));
}
function quoteArgs($val){
	$val	= str_replace('"', '\\"', $val);
	$val	= str_replace('(', '\\(', $val);
	$val	= str_replace(')', '\\)', $val);
	return $val;
}
function parsePageFn($matches)
{	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$data		= array();
	$baseCode	= $matches[1];
	@list($moduleName, $moduleData) = explode('=', $baseCode, 2);

	$bPriorityModule = $moduleName[0] == '!';
	if ($bPriorityModule) $moduleName = substr($moduleName, 1);
	
	//	name:val;nam2:val
	$d = explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if ($val){
			$name	= str_replace('.', '"]["', $name);
			$data[] = "\$module_data[\"$name\"] = \"$val\"; ";
		}else{
			$data[] = "\$module_data[] = \"$name\"; ";
		}
	}
	
	if ($data){
		//	new code
		$code = "\$module_data = array(); ";
		$code.= implode('', $data);
		$code.= "module(\"$moduleName\", \$module_data);";
	}else{
		$code = "module(\"$moduleName\");";
	}

	if (!$bPriorityModule) return "<? $code ?>";

	$GLOBALS['_CONFIG']['page']['compileLoaded'][] = "<? \$p = ob_get_clean(); $code echo \$p; ?>";
	return "<? ob_start(); ?>";
}
function parsePageValFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
	return "<?= @htmlspecialchars($val) ?>";
}

function parsePageValDirectFn($matches)
{
	$val = $matches[1];
	//	[value] => ['value']
	$val = preg_replace('#\[([^\]]*)\]#', "[\"\\1\"]", $val);
	return "<?= @$val ?>";
}
function parsePageCSS($matches)
{
	$val = $matches[1];
	return "<? module(\"page:style\", '$val') ?>";
}

?>
<? // Module module_libFile loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/_lib/module_libFile.php ?>
<?
function echoEncode($value){
	echo $value;//iconv("windows-1251", "utf-8", $value);
}
//	Вывести на экран массив, как XML документ
//	Использовать знак '@' в дочерних нодах для записи как аттрибуты
function writeXML(&$xml, $date=NULL){
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
	while(list($tag, $child)=each($xml)){
		if (is_int($tag)){
			writeXMLtag($child);
			continue;
		}
		if (!is_array($child)){
			if ($tag[0]=='!'){
				$tag = substr($tag, 1);
				echoEncode("<$tag><![CDATA[$child]]></$tag>");
			}else{
				echoEncode("<$tag>".htmlspecialchars($child)."</$tag>");
			}
			continue;
		}
		
		$tags = array();
		echoEncode("<$tag");
		while(list($name, $value)=each($child)){
			
			if ($name[0]!='@'){
				$tags[$name]=$value;
				continue;
			}
			$name = substr($name, 1);
			$name = $name;
			$value= htmlspecialchars($value);
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
*/	module('module_translit', &$name);
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

//	Удалить дерево директорий с файлами
function delTree($dir, $bRemoveBase = true, $bUseRename = false)
{
	$dir	= rtrim($dir, '/');
	if ($bUseRename){
		$rdir	= "$dir.del";
		@rename($dir, $rdir);
		if (!$bRemoveBase) makeDir($dir);
		$dir	= $rdir;
	}

	@$d		= opendir($dir);
	if (!$d) return;
	
	while(($file = readdir($d)) != null){
		if ($file == '.' || $file == '..') continue;
		$file = "$dir/$file";
		if (is_file($file))	unlink($file);
		else
		if (is_dir($file)) delTree($file, true, false);
	}
	@closedir($d);
	if ($bRemoveBase || $bUseRename) @rmdir($dir);
}

?>
<? // Module module_libImage loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/_lib/module_libImage.php ?>
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
				
				if ($comment) file_put_contents("$file.shtm", $comment);
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
function isMaxFileSize($path){
	if (!defined('gd2')) return true;
	@list($w,$h) = getimagesize($path);
	if (!$w || !$h) return true;
	return $w*$h > 1500*1500*3;
//	return $w*$h*3 < 500*500*3;
	return @filesize($path) > 150*1024;
}
//	Изменить размер файла
function resizeImage($srcPath, $w, $h, $dstPath=''){
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
function  loadImage($src){
	list($file, $ext)=fileExtension($src);
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
function displayThumbImage($src, $w, $options='', $altText='', $showFullUrl='', $rel=''){
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
	$dst = htmlspecialchars($dst);
	if (!$altText) $altText = @file_get_contents("$src.shtm");
	$altText = htmlspecialchars($altText);
	$options .= " alt=\"$altText\"";
	
	$ctx = "<img src=\"$dst\" width=\"$w\" height=\"$h\"$options />";
	if ($showFullUrl) showPopupImage($src, $showFullUrl, $ctx, $altText, $rel);
	else echo $ctx;
	
	return $dst;
}
//	Вывести картинку в виде уменьшенной копии, с наложением маски прозрачности (формат png)
function displayThumbImageMask($src, $maskFile, $options='', $altText='', $showFullUrl='', $rel='')
{
	if (isMaxFileSize($src)) return false;

	$maskFile	= localHostPath."/$maskFile";
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
	$altText = htmlspecialchars($altText);
	$altText = " alt=\"$altText\"";
	echo "<img src=\"$src\" width=\"$w\" height=\"$h\"$altText$options />";
	return true;
}
function showPopupImage($src, $showFullUrl, $ctx, $alt='', $rel=''){
	module('script:lightbox');
	$rel = $rel?"lightbox[$rel]":'lightbox';
	echo "<a href=\"$showFullUrl\" class=\"zoom\" title=\"$alt\" target=\"image\" rel=\"$rel\">", $ctx, "<span></span></a>";
}
function clearThumb($folder){

	$files = getFileList($folder, '^thumb', false);
	while(list(,$path)=each($files)) delTree($path);
	
	$files = getFileList($folder, '', false);
	while(list(,$path)=each($files)) clearThumb($path);
}
?>
<? // Module module_debug loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/_module.debug/module_debug.php ?>
<?
//	module user
function module_debug(&$fn, &$data){
	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("debug_$fn");
	return $fn?$fn($val, $data):NULL;
}
function debug_executeTime(){
	echo 'Время выполнения: ', round(getmicrotime() - sessionTimeStart, 3), ' сек.';
}
?>

<? // Module module_gzip loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_core/_module.gzip/module_gzip.php ?>
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
<? // Module module_database loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_database/module_database.php ?>
<?
class dbRow
{
//	main functions
	function dbRow($table = '', $key = '', $dbLink = 0, $alter = NULL)
	{
		$this->max		= 0;
		$this->table	= dbTableName($table);;
		$this->key 		= $key;
		$this->dbLink 	= $dbLink?$dbLink:dbConnect();
		if ($alter) $this->alter($alter);
	}
	function __destruct()	{ @mysql_free_result ($this->res); }
	function reset()		{ $this->order = $this->group = $this->fields = ''; }
	function open($where='', $max=0, $from=0, $date=0){
		return $this->doOpen($where, $max, $from, $date);
	}
	function openIN($ids){
		$ids	= makeIDS($ids);
		$key 	= makeField($this->key());
		return $this->open("$key IN ($ids)");
	}
	function openID($id){
		$key= makeField($this->key());
		$id	= (int)$id;
		$this->open("$key=$id");
		return $this->next();
	}

	function delete($id)	{ $this->doDelete($id);	}
	function deleteByKey($key, $id){
		$key	= makeField($key);
		$table	= $this->table;
		$ids	= makeIDS($id);
		$sql	= "DELETE FROM $table WHERE $key IN ($ids)";
		return $this->exec($sql);
	}
	function sortByKey($sortField, &$orderTable)
	{
		if (!is_array($orderTable)) return;
		
		$sortField	= makeField($sortField);
		$key		= $this->key();
		$table		= $this->table();
		
		$nStep	= 0;
		$sql	= '';
		foreach($orderTable as $id){
			$nStep += 1;
			makeSQLValue($id);
			$this->exec("UPDATE $table SET $sortField = $nStep WHERE $key = $id");
		}
	}
	function renumberKey($key, $sql = '', $nStep = 100)
	{
		$key	= makeField($key);
		$table	= $this->table();
		
		if ($sql) $sql = " WHERE $sql";
		$this->exec("SET @renameCounter = $nStep");
		$this->exec("UPDATE $table SET $key = @renameCounter := @renameCounter + $nStep$sql ORDER BY $key");
	}
	function selectKeys($key, $sql = '')
	{
		$key	= makeField($key);
		$table	= $this->table();
		if (is_array($sql)) $sql = implode(' AND ', $sql);
		if ($sql) $sql = " WHERE $sql";

		$res = dbExec("SELECT GROUP_CONCAT(DISTINCT $key SEPARATOR ', ') AS ids FROM $table $sql", 0, 0, $this->dbLink);
		$data= dbResult($res);
		return @$data['ids'];
	}
	function table()		{ return $this->table; }
	function key()			{ return $this->key; }
	function exec($sql, $max=0, $from=0){
		$this->maxCount = $this->ndx = 0;
		return $this->res = dbExec($sql, $max, $from, $this->dbLink);
	}
	function execSQL($sql)	{ return dbExec($sql, 0, 0, $this->dbLink); }
	function next()			{ 
		if ($this->max && $this->maxCount >= $this->max) return false;
		$this->maxCount++;
		$this->ndx++;
		$this->data = dbResult($this->res);
		return $this->rowCompact();
	}
	function rows()			{ return @dbRows($this->res); }
	function seek($row)		{ @dbRowTo($this->res, $row); }
//	base functions
	function doOpen($where='', $max=0, $from=0, $date=0)
	{
		return @$this->exec($this->makeSQL($where, $date), $max, $from);
	}
	function makeSQL($where, $date = 0)
	{
		$table = makeField($this->table());
		
		if (@$this->fields) $fields = $this->fields;
		else $fields = '*';
		
		@$group = $this->group;
		
		if (is_array($where)){
			if (@$val = $where[':from'])
			{
				unset($where[':from']);
				$table = array();
				foreach($val as $tableName => $tableAlias){
					$table[] = dbTableName($tableName). " $tableAlias";
				}
				$table = implode(', ', $table);
			}
			if (@$val = $where[':fields']){
				unset($where[':fields']);
				$fields = $val;
			}
			if (@$val = $where[':group']){
				unset($where[':group']);
				$group = $val;
			}
			$where = implode(' AND ', $where);
		}
		
		if ($where) $where = "($where)";
		
		if ($date){
			if ($where) $where .= ' AND ';
			$where .= 'lastUpdate > '.makeSQLDate($date);
		}
		
		if (@$sql=$this->sql) $where.= $where?" AND $sql":$sql;
		if ($where) $where = "WHERE $where";
		if (@$order = $this->order) $order = "ORDER BY $order";
		if ($group)	$group = "GROUP BY $group";
		
		return "SELECT $fields FROM $table $where $group $order";
	}
	function rowCompact(){
		if (@$this->data['fields'] && !is_array($this->data['fields']))
			@$this->data['fields'] = unserialize($this->data['fields']);
		if (@$this->data['document'] && !is_array($this->data['document']))
			@$this->data['document'] = unserialize($this->data['document']);
		@reset($this->data);
		return $this->data;
	}
	function doDelete($id)
	{
		$table	=	$this->table();
		$key 	=	$this->key();
		$id		=	makeIDS($id);
		$key 	=	makeField($key);
		$table	=	makeField($table);
		$this->execSQL("DELETE FROM $table WHERE $key IN ($id)");
	}
	function id()		{ return @$this->data[$this->key()]; }
	function update($data, $doLastUpdate=true)
	{
		$table=$this->table();
		$key = $this->key();
		@$id = makeIDS($data['id']);
		unset($data['id']);

		reset($data);
		while(list($field, $value)=each($data)){
			if (is_string($value)){
				if (function_exists('makeSQLLongDate') && ($date = makeSQLLongDate($value))){
					$data[$field]=$date;
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

		if ($doLastUpdate) $data['lastUpdate']=makeSQLDate(mktime());
		if ($id){
			$k = makeField($key);
			if (!$this->updateRow($table, $data, "WHERE $k IN($id)")) return 0;
		}else
			$id = $this->insertRow($table, $data);
//echo mysql_error();			
		return $id?$this->data[$key]=$id:0;
	}
	//	util functions
	function setValue($id, $field, $val, $doLastUpdate=true){
		$data=array('id'=>$id, $field=>$val);
		return $this->update($data, $doLastUpdate);
	}
	function setValues($id, $data, $doLastUpdate=true){
		$data['id']=$id;
		return $this->update($data, $doLastUpdate);
	}
	function insertRow($table, $array){
//	print_r($array); die;
		reset($array);
		$table = makeField($table);
		$fields=''; $comma=''; $values='';
		while(list($field, $value)=each($array)){
			$field=makeField($field);
			$fields.="$comma$field";
			$values.="$comma$value";
			$comma=',';
		}
		return dbExecIns("INSERT INTO $table ($fields) VALUES ($values)", 0, $this->dbLink);
	}
	function updateRow($table, $array, $sql){
		reset($array);
		$table = makeField($table);
		$command=''; $comma='SET ';
		while(list($field, $value)=each($array)){
			$field=makeField($field);
			$command.="$comma$field=$value";
			$comma = ',';
		}
		return $this->execSQL("UPDATE $table $command $sql");
	}
	function folder($id=0){
		if (!$id) $id = $this->id();
		if ($id){
			@$fields= $this->data['fields'];
			if (!is_array($fields)) @$fields = unserialize($fields);
			@$path	= $fields['filepath'];
			if ($path) return $this->images.'/'.$path;
		}
		$userID = function_exists('userID')?userID():0;
		return $this->images.'/'.($id?$id:"new$userID");
	}
	function url($id=0)		{ return $this->url.($id?$id:$this->id()); }
	function alter($fields)	{ dbAlterTable($this->table, $fields, false); }
};

function makeIDS($id)
{
	if (!is_array($id)) $id=explode(',',$id);
	$result = array();
	reset($id);
	while(list($ndx, $val)=each($id))
	{
		if (preg_match('#^\d+$#', $val)){
			$val = (int)$val;
		}else{
			if ($val) makeSQLValue($val);
		}
		if ($val) $result[$val] = $val;
	}
	if (count($result))	return implode(',',$result);
	return 0;
}

////////////////////////////////////
//	создать папку по данному пути
function createDir($path){
	$dir	= '';
	$path	= explode('/',str_replace('\\', '/', $path));
	while(list(,$name)=each($path)) @mkdir($dir.="$name/");
}

function makeDateStamp($val){
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4}$)#', $val, $v)){
		list(,$d,$m,$y) = $v;
		return mktime(0, 0, 0, $m, $d, $y);
	}else
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i) = $v;
		return mktime($h, $i, 0, $m, $d, $y);
	}
	if (preg_match('#^(\d{1,2})\.(\d{1,2})\.(\d{4})\s+(\d{1,2}):(\d{1,2}):(\d{1,2}$)#', $val, $v)){
		list(,$d,$m,$y,$h,$i,$s) = $v;
		return mktime($h, $i, $s, $m, $d, $y);
	}
	return 0;
}
function dateStamp($val){
	if (!$val) return;
	return date('d.m.Y H:i', $val);
}

?>
<? // Module module_mysql loaded from  /usr/home/18508/getbest.ru/html/DEV/_modules/_module_database/module_mysql.php ?>
<?
//	Класс для манипуляции базой данных MySQL
//	Open database
function dbConnect($bCreateDatabase = false)
{
	if (defined('dbConnect')) return $GLOBALS['dbConnection'];
	define('dbConnect', true);

	//	Смотрим локальные настройки базы данных
	$ini		= getCacheValue('ini');
	@$dbIni		= $ini[':db'];
	//	Если их нет, пробуем глобальные
	if (!is_array($dbIni)){
		$ini		= getGlobalCacheValue('ini');
		//	Получим глобальные правила
		$globalDb	= $ini['globalSiteDatabase'];
		if (!is_array($globalDb)) $globalDb = array();
		//	Пройдемся по правилам
		foreach($globalDb as $rule => $dbKey){
			if (!preg_match("#$rule#", $_SERVER['HTTP_HOST'])) continue;
			//	Если правило подходит, возмем значение из нового ключа
			@$dbIni	= $ini[$dbKey];
			break;
		}
		//	Если настроек не найдено, пробуем стандартные
		if (!is_array($dbIni))
			@$dbIni = $ini[':db'];
	}
	@$dbhost	= $dbIni['host'];
	@$dbuser	= $dbIni['login'];
	@$dbpass	= $dbIni['passw'];
	@$db		= $dbIni['db'];

	$GLOBALS['dbConnection'] = mysql_connect($dbhost, $dbuser, $dbpass);
	if (mysql_error()){
		module('message:sql:error', mysql_error());
		module('message:error', 'Ошибка открытия базы данных.');
		return;
	}
//	@dbExec("SET character_set_results = 'cp1251'");
//	@dbExec("SET character_set_client = 'cp1251'");
	if ($bCreateDatabase) @dbExec("CREATE DATABASE `$db`");
	@dbExec("SET NAMES UTF8");
	dbSelect($db, $GLOBALS['dbConnection']);
	return $GLOBALS['dbConnection'];
}
function dbTablePrefix()
{
	$ini	= getCacheValue('ini');
	@$prefix= $ini[':db']['prefix'];
	$url	= preg_replace('#[^\d\w]+#', '_', getSiteURL());
	if (!$prefix) return $url.'_';
	return "$url_$prefix".'_';
}
function dbTableName($name){
	$prefix = dbTablePrefix();
	return "$prefix$name";
};

function dbExec($sql, $rows=0, $from=0, &$dbLink = NULL){// echo $sql;
	if(defined('_debug_')) echo "<div class=\"log\">$sql</div>";
	module('message:sql:trace', $sql);
	$res = @mysql_query($rows?"$sql LIMIT $from, $rows":$sql);
	module('message:sql:error', mysql_error());
	return $res;
}
function dbSelect($db, &$dbLink)	{ return mysql_select_db($db); }
function dbRows($id)				{ return mysql_num_rows($id);}
function dbResult($id)				{ return @mysql_fetch_array($id, MYSQL_ASSOC);}
function dbRowTo($id, $row)			{ return @mysql_data_seek($id, $row);}
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

//	Подготавливаются данные в соотвествии с правилами SQL
function makeSQLValue(&$val){
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
		$val=serialize($val);
	default:
		if (strncmp($val, 'FROM_UNIXTIME(', 14)==0) break;
		if (strncmp($val, 'DATE_ADD(', 9)==0) break;
		$val = @mysql_real_escape_string($val);
		$val = "'$val'";
		break;
	}
}
function sqlDate($val)		{ return date('Y-m-d H:i:s', (int)$val); }
function makeSQLDate($val)	{ return "FROM_UNIXTIME($val)"; }
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
	@$d = mktime($hour, $min, $sec, $month, $day, $year);
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

//	fields $fields[name]=array{'type'=>'int', 'length'=>'11'};.....
function dbAlterTable($table, $fields, $bUsePrefix = true, $databaseEngine = '')
{
	if (!$databaseEngine) $databaseEngine = 'MyISAM ROW_FORMAT=FIXED';
	dbConnect(true);
//define('_debug_', true);
	if ($bUsePrefix) $table = dbTableName($table);

	$alter	= array();
	$rs		= dbExec("DESCRIBE $table");
	if ($rs){
		while($data = dbResult($rs))
		{
			$name	= $data['Field'];
			@$f 	= $fields[$name];
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
		if (!$sql) return;
		
		$sql = implode(', ', $sql);
//		echo("ALTER TABLE $table $sql");
		dbExec("ALTER TABLE $table $sql");
		module('message:sql', "Updated table `$table`");
//		echo mysql_error();
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
	dbExec("CREATE TABLE $table ($sql) COLLATE='utf8_general_ci' ENGINE=$databaseEngine;");
	module('message:sql', "Created table `$table`");
}
function dbAlterCheckField(&$alter, &$need, &$now, $bCreate = false)
{	
	if (!isset($need['Type']))	@$need['Type']	= $now['Type'];
	if (!isset($need['Null']))	$need['Null']	= 'YES';
	if (!isset($need['Key']))	$need['Key']	= '';
	if (!isset($need['Extra']))	$need['Extra']	= '';
	if (!isset($need['Default']))$need['Default']=NULL;

	$bChanged = false;
	
//	print_r($now);
//	print_r($need);
	
	$bChanged |= @$need['Type'] != @$now['Type'];
	$bChanged |= isset($need['Null']) 	&& @$need['Null'] 		!= @$now['Null'];
	$bChanged |= isset($need['Default'])&& @$need['Default'] 	!= @$now['Default'];
	$bChanged |= isset($need['Key'])	&& @$need['Key'] 		!= @$now['Key'];
	
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
