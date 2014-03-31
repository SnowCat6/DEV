<?
//	Задать папку для импорта файлов
define('importFolder', localHostPath.'/_exchange');

function module_import($fn, &$data)
{
	if (!access('write', 'doc:')) return;

	@list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("import_$fn");
	return $fn?$fn($val, $data):NULL;
}
function parseInt(&$val){
	$v = preg_replace('#[^\d.,]#', '', $val);
	$v = (float)str_replace(',',  '.', $v);
	return $v;
}
function import_tools($fn, &$data){
	if (!access('add', 'doc:product')) return;
	$data['Импорт товаров']		= getURL('import');
	$data['Создать YandexXML']	= getURL('yandex-export');
}
function importPrepareBulk(&$synch)
{
	$cache		= $synch->getValue('importCache');
	$bComplete	= importPrepareBulk2($synch, $cache);
	$synch->setValue('importCache', $cache);
	return $bComplete;
}
//	Завершить импорт, скрыть не обновленные товары, показать ранее скрытые
function importCommitBulk(&$synch)
{
	$cache		= $synch->getValue('importCache');
	$bComplete	= importCommitBulk2($synch, $cache);
	$synch->setValue('importCache', $cache);
	return $bComplete;
}
/****************************/
function importPrepareBulk2(&$synch, &$cache)
{
	if (!importPrepareGroups($synch, $cache))	return false;
	if (!importPrepareProduct($synch, $cache))	return false;
	
	return true;
}
function importCommitBulk2(&$synch, &$process)
{
	if (sessionTimeout() < 5) return false;

	$cache			= &$process['cacheProduct'];
	$updateProduct	= $cache['updateProduct'];

	$db	= module('doc');
	$db->sql	= '';
	$sql= doc2sql(array('id' => $updateProduct));
	$sql= $db->makeRawSQL($sql);
	$sql= "UPDATE SET visible=1 $sql[from] $sql[join] $sql[where]";
	$db->exec($sql);

	$synch->log('Imported: '.count($updateProduct));
	
	return true;
}
/********************************/
function importPrepareGroups(&$synch, &$process)
{
	if ($process['cacheGroupComplete']) return true;
	
	$db		= module('doc');
	$cache	= &$process['cacheGroup'];
	$cacheParent	= &$process['cacheParents'];
	//	Занесение в кеш импортированых групп товаров
	$s	= array();
	$s['type']				= 'catalog';
//	$s['prop'][':import']	= 'price';
	//	Открыть базу
	$db->sql= '';
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		if (sessionTimeout() < 5) return false;
		//	Аолучить свойства товара
		$id		= $db->id();

		@$prop	= $data['fields'];
		@$prop	= $prop['any'];
		@$prop	= $prop['import'];

		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':importArticle'];

		//	Проверить на наличие дублей в базе	
		$a	= ":$article";	
		if (!$article || isset($cache[$a])){
			$db->clearCache();
			continue;
		}
		$cache[$a] = $id;
		
		//	Запомним родительский каталог, если он есть
		@$parent	= $prop[':importParent'];
		if ($parent) $cacheParent[$id][$parent] = true;
		
		//	Кешируем все свойства, чтобы не обновлять базу не измененными свойствами
		@$thisProperty	= &$process['cacheProperty'][$id];
		$thisProperty	= array();

		$prop 	= module("prop:get:$id");
		foreach($prop as $name => &$val){
			$thisProperty[$name] = $val['property'];
		}

		$db->clearCache();
	}
	$process['cacheGroupComplete']	= true;
	
	$nCount	= count($cache);
	$synch->log("Cached $nCount catalogs");
	
	return true;
}
function importPrepareProduct(&$synch, &$process)
{
	if ($process['cacheProductComplete']) return true;
	
	$db		= module('doc');
	$cache			= &$process['cacheProduct'];
	$cacheParent	= &$process['cacheParents'];
	$cache['updateProduct']	= array();
	//	Занесение в кеш импортированых групп товаров
//	define('_debug_', true);
	$s	= array();
	$s['type']				= 'product';
//	$s['prop'][':import']	= 'price';

	$db->sql = '';
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		if (sessionTimeout() < 5) return false;

		$id		= $db->id();

		@$prop	= $data['fields'];
		@$prop	= $prop['any'];
		@$prop	= $prop['import'];

		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':importArticle'];
		
		//	Проверить на наличие дублей в базе	
		$a	= ":$article";	
		if (!$article || isset($cache[$a])){
			$db->clearCache();
			continue;
		}
		$cache[$a] = $id;
		

		//	Кешируем все свойства товара, чтобы не обновлять базу не измененными свойствами
		@$thisProperty	= &$process['cacheProperty'][$id];
		$thisProperty	= array();

		$prop 	= module("prop:get:$id");
		
		@$parent= $prop[':parent'];
		if ($parent) $cacheParent[$id][$parent['property']] = true;
		
		foreach($prop as $name => &$val){
			$thisProperty[$name] = $val['property'];
		}
		//	Псевдосвойства
		$thisProperty[':title'] = $data['title'];
		$thisProperty[':price'] = docPrice($data);

		$db->clearCache();
	}
	$process['cacheProductComplete']	= true;
	
	$nCount	= count($cache);
	$synch->log("Cached $nCount products");

	return true;
}
/*****************************************/
function importCatalog(&$synch, &$property){
	$cache		= $synch->getValue('importCache');
	$bComplete	= importCatalog2($synch, $cache, $property);
	$synch->setValue('importCache', $cache);
	$synch->setValue('statistic',	$cache['statistic']);
	return $bComplete;
}
function importCatalog2(&$synch, &$process, &$property)
{
	//	Закрывающий тег
	@$article	= $property['id'];		//	Артикул
	@$parent	= $property['parentId'];//	Родительский объект
	@$name		= $property['name'];
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[":$article"];
	@$parentId	= $cache[":$parent"];


	$statistic	= &$process['statistic']['category'];

	$d			= array();
	@$thisProp	= $property[':property'];
	if (!is_array($thisProp)) $thisProp = array();
	
	if ($id){
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
//				echo "$parent:$article -";
				$d['fields']['any']['import'][':importArticle']	= $article;
				$d['fields']['any']['import'][':importParent']	= $parentId;
				$d[':property'][':parent']						= $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		
		@$cacheProp	= &$process['cacheProperty'][$id];
		foreach($thisProp as $name => &$prop){
			$diff = array_diff(explode(', ', $prop), explode(', ', $cacheProp[$name]));
			if (!$diff) continue;
			$d[':property'][$name] = $prop;
		}
		
		if ($d){
			$id = module("doc:update:$id:edit", $d);
			if ($id){
				@$statistic['update'] += 1;
			}else{
				$msg	= m('display:!message');
				$synch->log("Error update catalog '$name': $nsg");
			}
		}else{
			@$statistic['pass'] += 1;
		}
	}else{
		$d['title']		= $name;
		$d[':property']	= $thisProp;
		$d[':property'][':import']						= 'price';
		$d['fields']['any']['import'][':importArticle']	= $article;
		$d['fields']['any']['import'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:catalog", $d);
		if ($id){
			@$statistic['add'] += 1;
			$cache[":$article"] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
			$msg	= m('display:!message');
			$synch->log("Error import catalog '$name': $msg");
		}
	}
}
/*******************************/
function importProduct(&$synch, &$property){
	$cache		= $synch->getValue('importCache');
	$bComplete	= importProduct2($synch, $cache, $property);
	$synch->setValue('importCache',	$cache);
	$synch->setValue('statistic',	$cache['statistic']);
	return $bComplete;
}
function importProduct2(&$synch, &$process, &$property)
{
	@$article		= $property['id'];
	@$name			= $property['name'];
	if (!$article || !$name)	return;

	@$parentArticle	= $property['categoryId'];
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];
	$statistic	= &$process['statistic']['product'];
	$updateProduct	= &$cache['updateProduct'];

	@$id		= $cache[":$article"];
	@$parentId	= $cacheParent[":$parentArticle"];

	$d			= array();
	@$price		= parseInt($property['price']);
	@$thisProp	= $property[':property'];
	if (!is_array($thisProp)) $thisProp = array();
	
	if ($id){
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
				$d['fields']['any']['import'][':importArticle']	= $article;
				$d['fields']['any']['import'][':importParent']	= $parentId;
				$d[':property'][':parent']						= $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		
		@$cacheProp	= &$process['cacheProperty'][$id];
		if ($name != $cacheProp[':title']){
			$d['title'] = $name;
			$cacheProp[':title'] = $name;
		}
		if ((float)$price != (float)$cacheProp[':price']){
			$d['price'] = $price;
		}

		foreach($thisProp as $name => &$prop)
		{
			$propVal	= $prop?explode(', ', $prop):array();
			$cacheVal 	= $cacheProp[$name]?explode(', ', $cacheProp[$name]):array();
			foreach($propVal as &$value)
			{
				if (!$value) continue;
				if (is_int(array_search($value,		$cacheVal))) continue;
				if (is_int(array_search((int)$value,$cacheVal))) continue;
				$d[':property'][$name] = $prop;
				break;
			}
		}

		if ($d){
			$db		= module('doc');
			$d2		= $db->openID($id);
			dataMerge($d['fields']['any']['import'], $d2['fields']['any']['import']);
			$d['fields']['any']['import'][':raw']	= $property;
			
			$iid	= module("doc:update:$id:edit", $d);
			if ($iid){
				@$statistic['update']  += 1;
				$updateProduct[$id]		= $id;
			}else{
				@$statistic['error'] += 1;
				$msg	= m('display:!message');
				$synch->log("Error update product '$name': $msg");
			}
		}else{
			@$statistic['pass'] += 1;
			$updateProduct[$id]	= $id;
		}
		$process['imported'][] = $id;
	}else{
		$d['title']		= $name;
		$d['price'] 	= $price;
		$d[':property']	= $thisProp;
		$d[':property'][':import']						= 'price';
		$d['fields']['any']['import'][':importArticle']	= $article;
		$d['fields']['any']['import'][':importParent']	= $parentId;
		$d['fields']['any']['import'][':raw']			= $property;
		$id = module("doc:update:$parentId:add:product", $d);
		if ($id){
			$process['imported'][] = $id;
			@$statistic['add'] += 1;
			$updateProduct[$id]	= $id;
			$cache[":$article"]	= $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
			$msg	= m('display:!message');
			$synch->log("Error import product '$name': $msg");
		}
	}
}
?>
