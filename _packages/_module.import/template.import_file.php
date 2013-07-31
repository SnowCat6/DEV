<? function import_file($val, &$process){
	//	Повторять пока есть время
	while(sessionTimeout() > 5)
	{
		event('import.file', $process);
		importLog($process, "Стадия: $process[step]", 'status');
		//	Этап импорта
		switch(@$process['step'])
		{
		//	Любое значение, подготовить импорт
		default:
			//	Вернуть true если требуется продолжение
			makeImportPrepare($process);
			$process['step'] = 'cacheGroups';
			break;
		//	Стадия импорта
		case 'cacheGroups':
			//	Вернуть true если требуется продолжение
			if (makeImportCacheGroups($process))
				$process['step'] = 'cacheProduct';
			break;
		//	Стадия импорта
		case 'cacheProduct':
			//	Вернуть true если требуется продолжение
			if (makeImportCacheProduct($process))
				$process['step'] = 'import';
			break;
		//	Стадия импорта
		case 'import':
			$ext = explode('.', $process['importFile']);
			$ext = strtolower(end($ext));
			$bComplete = moduleEx("import:$ext", $process);
			//	Выдать лог исполнения
			$statistic	= $process['statistic'];
			$category	= $statistic['category'];
			importLog($process, "Импортировано разделов: добавлено <b>$category[add]</b>, обновлено <b>$category[update]</b>, пропущено <b>$category[pass]</b>, ошибок  <b>$category[error]</b>", 'categoryIpdate');
			$product	= $statistic['product'];
			importLog($process, "Импортировано товаров: добавлено <b>$product[add]</b>, обновлено <b>$product[update]</b>, пропущено <b>$product[pass]</b>, ошибок  <b>$product[error]</b>", 'productUpdate');
			if ($bComplete){
				$process['step'] = 'completing';
				break;
			}
			//	Вернуть false если требуется продолжение
			return false;
		//	Стадия импорта
		case 'completing':
			return makeImportComplete($process);
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

		@$prop		= $data['fields'];
		@$prop		= $prop['any'];
		@$prop		= $prop['import'];

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
	return true;
}?>
<?
function makeImportComplete(&$process)
{
	if (sessionTimeout() < 5) return false;
	//	Пометить товары в соответствии с состоянием импорта
	//	Необходимо по завершении импорта скрыть товары без этого флага
/*
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

	if (sessionTimeout() < 10) return false;
	//	Обновить все документы
	//	Так-же выполняется при завершении импорта всех файлов
	m('doc:recompile');
*/	
	return true;
}?>
<?
function importCatalog(&$process, &$property)
{
	//	Закрывающий тег
	@$article	= $property['id'];		//	Артикул
	@$parent	= $property['parentId'];//	Родительский объект
	@$name		= $property['name'];
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[":$article"];
	@$parentId	= $cache[":$parent"];


	$statistic	= &$process['statistic']['category'];

	$d	= array();
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
?>

<?
function importProduct(&$process, &$property)
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
//			$diff = array_diff(explode(', ', $prop), explode(', ', $cacheProp[$name]));
//			if (!$diff) continue;
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

