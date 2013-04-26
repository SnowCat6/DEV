<? function import_file($val, &$process){
	//	Повторять пока есть время
	while(sessionTimeout() > 5)
	{
		event('import.file', &$process);
		importLog($process, "Стадия: $process[step]", 'status');
		//	Этап импорта
		switch(@$process['step'])
		{
		//	Любое значение, подготовить импорт
		default:
			//	Вернуть true если требуется продолжение
			makeImportPrepare(&$process);
			$process['step'] = 'cacheGroups';
			break;
		//	Стадия импорта
		case 'cacheGroups':
			//	Вернуть true если требуется продолжение
			if (makeImportCacheGroups(&$process))
				$process['step'] = 'cacheProduct';
			break;
		//	Стадия импорта
		case 'cacheProduct':
			//	Вернуть true если требуется продолжение
			if (makeImportCacheProduct(&$process))
				$process['step'] = 'import';
			break;
		//	Стадия импорта
		case 'import':
			if (module('import:xml', &$process)){
				$process['step'] = 'completing';
				break;
			}
			//	Вернуть false если требуется продолжение
			return false;
		//	Стадия импорта
		case 'completing':
			return makeImportComplete(&$process);
		}
		//	Если записать состояние не удалось, значит задача отменена, продолжения не надо
		if (!setImportProcess($process, false))
			return true;
	};
	return false;
}?>
<?
//	Подготовить кеш для импорта, загрузить данные
function makeImportPrepare(&$process)
{
	$process['cacheGroup']		= array();
	$process['cacheProduct']	= array();
	$process['cacheParents']	= array();
	$process['cacheProperty']	= array();
	$process['imported']		= array();
	
	$process['tagStack']		= array();
	$process['log']				= array();
	
	$process['statistic']		= array();
	$process['statistic']['category']['add']	= 0;
	$process['statistic']['category']['update']	= 0;
	$process['statistic']['category']['pass']	= 0;
	$process['statistic']['category']['error']	= 0;
	
	$process['statistic']['product']['add']		= 0;
	$process['statistic']['product']['update']	= 0;
	$process['statistic']['product']['pass']	= 0;
	$process['statistic']['product']['error']	= 0;
	
	//	Пометить товары как не имеющиеся в прайсе
	//	Необходимо по завершении импорта скрыть товары без этого флага
	//	Это значение должно устанавливаться в начале импорта множества файлов, пока поддержка только одного импорта
	$db		= module('doc');
	$table	= $db->table;
	$db->exec("UPDATE $table SET `importAvalible` = 0 WHERE `doc_type` = 'product'");
} ?>
<?
//	Кешировать группы товаров
function makeImportCacheGroups(&$process)
{
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
		//	Кешируем все свойства товара, чтобы не обновлять базу не измененными свойствами
		$thisProperty	= &$process['cacheProperty'][$id];
		if (isset($thisProperty)){
			$db->clearCache();
			continue;
		}
		$thisProperty = array();
		
		$prop 	= module("prop:get:$id");
		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':importArticle'];
		if (!isset($article['property'])) continue;
		
		//	У каталога может быть много артикулов, учтем это и запомним каждый, но правильно должен быть только один
		foreach(explode(', ', $article['property']) as $a){
			if (!$a) continue;
			if (isset($cache[$a])) $data = NULL;
			else $cache[$a] = $id;
		}
		if (!$data){
			$db->clear();
			continue;
		}
		
		//	Запомним родительский каталог, если он есть
		@$parent	= $prop[':importParent'];
		foreach(explode(', ', @$parent['property']) as $parent){
			if ($parent) $cacheParent[$id][$parent] = true;
		}
		foreach($prop as $name => &$val){
			$thisProperty[$name] = $val['property'];
		}
		//	Псевдосвойства
		$thisProperty[':title'] = $data['title'];
		
		$db->clearCache();
	}
	return true;
}?>
<?
//	Кешировать группы товаров
function makeImportCacheProduct(&$process)
{
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
		//	Кешируем все свойства товара, чтобы не обновлять базу не измененными свойствами
		@$thisProperty	= &$process['cacheProperty'][$id];
		if (isset($thisProperty)){
			$db->clearCache();
			continue;
		}
		$thisProperty = array();
		
		$prop 	= module("prop:get:$id");
		//	Запомнить код товара и артикул (оригинальный код прайса)
		@$article	= $prop[':importArticle'];
		if (!isset($article['property'])) continue;
		
		foreach(explode(', ', $article['property']) as $a){
			if (!$a) continue;
			if (isset($cache[$a])) $data = NULL;
			else $cache[$a] = $id;
		}
		if (!$data){
			$db->clearCache();
			continue;
		}

		@$parent	= $prop[':importParent'];
		foreach(explode(', ', $parent['property']) as $parent){
			$cacheParent[$id][$parent] = true;
		}
		foreach($prop as $name => &$val){
			$thisProperty[$name] = $val['property'];
		}
		//	Псевдосвойства
		$thisProperty[':title'] = $data['title'];
		$thisProperty[':price'] = docPrice($data);

		$db->clearCache();
	}
	return true;
}?>
<?
function makeImportComplete(&$process)
{
	if (sessionTimeout() < 5) return false;
	//	Пометить товары в соответствии с состоянием импорта
	//	Необходимо по завершении импорта скрыть товары без этого флага
	$db		= module('doc');
	$table	= $db->table;
	$imported = implode(',', $process['imported']);
	$db->exec("UPDATE $table SET `importAvalible` = 1 WHERE `doc_type` = 'product' AND `doc_id` IN ($imported)");
	//	Статистика
	$db->exec("SELECT count(*) AS cnt FROM $table WHERE `doc_type` = 'product' AND `importAvalible` <> `visible`  AND `visible` == 1");
	$d		= $db->next();
	@$hide	= (int)$d['cnt'];

	$db->exec("SELECT count(*) AS cnt FROM $table WHERE `doc_type` = 'product' AND `importAvalible` <> `visible` AND `visible` == 0");
	$d		= $db->next();
	@$show	= (int)$d['cnt'];
	//	Это надо сделать по завершении всех импортов, если источников прайса больше одного
	//	На данном этапе прайс может быть только один
	$db->exec("UPDATE $table SET `visible` =  `importAvalible` WHERE `doc_type` = 'product'");

	importLog($process, "Товаров скрыто: $hide");
	importLog($process, "Товаров показано: $show");

	if (sessionTimeout() < 15) return false;
	//	Обновить все документы
	//	Так-же выполняется при завершении импорта всех файлов
	m('doc:recompile');
	
	return true;
}?>
<?
function importCatalog(&$process, &$property)
{
	//	Закрывающий тег
	@$article	= ":$property[id]";			//	Артикул
	@$parent	= ":$property[parentId]";	//	Родительский объект
	@$name		= $property['name'];
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[$article];
	@$parentId	= $cache[$parent];
	@$cacheProp	= &$process['cacheProperty'][$id];
	$statistic	= &$process['statistic']['category'];

	$d	= array();
	if ($id){
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
				$d[':property'][':importParent'] = $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		if ($name != $cacheProp[':title']){
			$d['title'] = $name;
			$cacheProp[':title'] = $name;
		}
		if ($d){
			@$statistic['update'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}else{
			@$statistic['pass'] += 1;
		}
	}else{
		$d['title']		= $name;
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:catalog", $d);
		if ($id){
			@$statistic['add'] += 1;
			$cache[$article] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
		}
	}
}
?>

<?
function importProduct(&$process, &$property)
{
	@$article		= ":$property[id]";
	if (!$article)	return;
	@$name			= $property['name'];
	if (!$name)	return;

	@$parentArticle	= ":$property[categoryId]";
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];
	$statistic	= &$process['statistic']['product'];

	@$id		= $cache[$article];
	@$parentId	= $cacheParent[$parentArticle];

	$d			= array();
	@$price		= parseInt($property['price']);
	@$thisProp	= $property[':property'];
	if (!is_array($thisProp)) $thisProp = array();
	
	if ($id){
		@$cacheProp	= &$process['cacheProperty'][$id];
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent){
				$d[':property'][':importParent'] = $parentId;
				$process['cacheParents'][$id][$parentId] = true;
			}
		}
		if ($name != $cacheProp[':title']){
			$d['title'] = $name;
			$cacheProp[':title'] = $name;
		}
		if ((float)$price != (float)$cacheProp[':price']){
			echo (float)$price, ' ', (float)$cacheProp[':price'];
			$d['price'] = $price;
		}

		foreach($thisProp as $name => &$prop){
			$diff = array_diff(explode(', ', $prop), explode(', ', $cacheProp[$name]));
			if (!$diff) continue;
			$d[':property'][$name] = $prop;
		}

		if ($d){
			print_r($d);
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
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
		$id = module("doc:update:$parentId:add:product", $d);
		if ($id){
			$process['imported'][] = $id;
			@$statistic['add'] += 1;
			$cache[$article] = $id;
			$process['cacheParents'][$id][$parentId] = true;
		}else{
			@$statistic['error'] += 1;
			logData("import: Error add product", 'import');
		}
	}
}
?>
