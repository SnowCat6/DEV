<?
//	Задать папку для импорта файлов
define('importFolder', localHostPath.'/_exchange');

function module_import($fn, &$data)
{
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
	$data['Импорт товаров']	= getURL('import');
}
function importPrepareBulk(&$synch)
{
	$cache		= $synch->getValue('importCache');
	$bComplete	= importPrepareBulk2($cache);
	$synch->setValue('importCache', $cache);
	return $bComplete;
}
function importCommitBulk(&$synch)
{
	return true;
}
/****************************/
function importPrepareBulk2(&$cache)
{
	if (!importPrepareGroups($cache))	return false;
	if (!importPrepareProduct($cache))	return false;
	
	return true;
}
/********************************/
function importPrepareGroups(&$process)
{
	if ($process['cacheGroupComplete']) return true;
	
	$db		= module('doc');
	$cache	= &$process['cacheGroup'];
	$cacheParent	= &$process['cacheParents'];
	//	Занесение в кеш импортированых групп товаров
	$s	= array();
	$s['type']				= 'catalog';
	$s['prop'][':import']	= 'price';
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
	return true;
}
function importPrepareProduct(&$process)
{
	if ($process['cacheProductComplete']) return true;
	
	$db		= module('doc');
	$cache	= &$process['cacheProduct'];
	$cacheParent	= &$process['cacheParents'];
	//	Занесение в кеш импортированых групп товаров
//	define('_debug_', true);
	$s	= array();
	$s['type']				= 'product';
	$s['prop'][':import']	= 'price';

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
	return true;
}
?>
<?
/*****************************************/
function importCatalog(&$synch, &$property){
	$cache		= $synch->getValue('importCache');
	$bComplete	= importCatalog2($cache, $property);
	$synch->setValue('importCache', $cache);
	return $bComplete;
}
function importCatalog2(&$process, &$property)
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
			@$statistic['update'] += 1;
			$id = module("doc:update:$id:edit", $d);
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
		}
	}
}
/*******************************/
function importProduct(&$synch, &$property){
	$cache		= $synch->getValue('importCache');
	$bComplete	= importProduct2($cache, $property);
	$synch->setValue('importCache', $cache);
	return $bComplete;
}
function importProduct2(&$process, &$property)
{
	@$article		= $property['id'];
	@$name			= $property['name'];
	if (!$article || !$name)	return;

	@$parentArticle	= $property['categoryId'];
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];
	$statistic	= &$process['statistic']['product'];

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
			$propVal	= explode(', ', $prop);
			$cacheVal 	= explode(', ', $cacheProp[$name]);
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
			$iid = module("doc:update:$id:edit", $d);
			if ($iid){
				@$statistic['update'] += 1;
			}else{
				@$statistic['error'] += 1;
				logData("import: Error update product $id", 'import');
			}
		}else{
			@$statistic['pass'] += 1;
		}
		$process['imported'][] = $id;
	}else{
		$d['title']		= $name;
		$d['price'] 	= $price;
		$d[':property']	= $thisProp;
		$d[':property'][':import']						= 'price';
		$d['fields']['any']['import'][':importArticle']	= $article;
		$d['fields']['any']['import'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:product", $d);
		if ($id){
			$process['imported'][] = $id;
			@$statistic['add'] += 1;
			$cache[":$article"] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
			logData("import: Error add product", 'import');
		}
	}
}
?>
