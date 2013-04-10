<?
function import_doImport($val, $files)
{
	if ($val)
	{
		if (!is_array($files)) return;
		
		switch($val){
		//	Перезапустить импорты указаных файлов
		case 'create':
			// Импортировать все файлы и массиве
			foreach($files as $file){
				getImportProcess($file, true);
			}
		//	Задачи созданы, обработать
		break;
		//	Остановтить импорт указаных файлов
		case 'delete':
			// Удалить все файлы и массиве
			foreach($files as $file){
				$process	= getImportProcess($file);
				$baseDir	= $process['baseDir'];
				delTree($baseDir);
			}
		//	Задачи остановлены, можно выйти
		return;
		//	Если команда неизвестна, ничего не делать
		default:
			return;
		}
	}else{
		$files = array_keys(getFiles(importFolder, 'xml$'));
	}

	$baseDir	= importFolder;
	//	Файл блокировки импорта
	$lockFile	= "$baseDir/lock.txt";
	//	Если файл существует, и время его создания не превысило таймаут, то не обрабатываем
	//	Выйти, ибо импорт уже идет
	if (is_file($lockFile) && mktime() - filemtime($lockFile) < (int)ini_get('max_execution_time')) return;
	//	Создать файл блокировки
	file_put_contents_safe($lockFile, $lockFile);
	
	// Импортировать все файлы и массиве
	foreach($files as $file)
	{
		$path	= importFolder."/$file";
		if (!is_file($path)) continue;
		//	Получить данные по импорту
		$process = getImportProcess($file);

		//	Импортировать
		$bCompleted	= makeImport($process);
		//	Есои импорт не завершен, то вывести страницу и продолжить импорт
		if (!$bCompleted){
			//	Удалить блокировку
			@unlink($lockFile);
			return setImportProcess($process, false);
		}
		//	Если импорт завершен, заисать результат, продолжить со следующим файлом
		setImportProcess($process, true);
	}
	//	Удалить блокировку
	@unlink($lockFile);
}
?>
<? function makeImport(&$process)
{
	//	Повторять пока есть время
	while(sessionTimeout() > 5)
	{
		importLog($process, "Стадия: $process[step]");
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
			//	Вернуть true если импорт завершен
			if (makeImportImport(&$process)) return true;
			//	Вернуть false если требуется продолжение
			return false;
		}
		//	Если записать состояние не удалось, значит задача отменена, продолжения не надо
		if (!setImportProcess($process, false))
			return true;
	};
	return false;
}
?>
<?
//	Подготовить кеш для импорта, загрузить данные
function makeImportPrepare(&$process)
{
	$process['cacheGroup']		= array();
	$process['cacheProduct']	= array();
	$process['cacheParents']	= array();
	
	$process['tagStack']		= array();
	$process['log']				= array();
	$process['statistic']		= array();
} ?>
<?
//	Кешировать группы товаров
function makeImportCacheGroups(&$process)
{
	$db = module('doc');
	//	Занесение в кеш импортированых групп товаров
	$s	= array();
	$s['type']				= 'catalog';
	$s['prop'][':import']	= 'price';
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		if (sessionTimeout() < 5) return false;
		
		$id		= $db->id();
		$prop 	= module("prop:get:$id");
		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':article'];
		if (isset($article['property'])){
			$article= "$article[property]";
			@$process['cacheGroup'][$article] = $id;
		}
		@$parent	= $prop[':parent'];
		@$parent	= explode(', ', $parent['property']);
		foreach($parent as $p){
			$process['cacheParents']["$id:$p"] = $p;
		}
		$db->clearCache();
	}
	return true;
}?>
<?
//	Кешировать группы товаров
function makeImportCacheProduct(&$process)
{
	$db = module('doc');
	//	Занесение в кеш импортированых групп товаров
//	define('_debug_', true);
	$s	= array();
	$s['type']				= 'product';
	$s['prop'][':import']	= 'price';

	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		if (sessionTimeout() < 5) return false;
		
		$id		= $db->id();
		$prop 	= module("prop:get:$id");
		//	Запомнить код товара и артикул (оригинальный код прайса)
		@$article	= $prop[':article'];
		if (isset($article['property'])){
			$article= $article['property'];
			@$process['cacheProduct'][$article] = $id;
		}
		@$parent	= $prop[':parent'];
		@$parent	= explode(', ', $parent['property']);
		foreach($parent as $p){
			$process['cacheParents']["$id:$p"] = $p;
		}
		$db->clearCache();
	}
	return true;
}?>
<?
//	Импортировать прайс
function makeImportImport(&$process)
{
	$ctx	= '';
	$row	= 0;
	
	$f	= fopen($process['importFile'], 'r');
	fseek($f, $process['offset']);
	
	while(!feof($f) && sessionTimeout() > 5)
	{
		$thisOffset	= ftell($f);
		$val		= fread($f, 1*1024*1024);
		$nParse		= 0;

		while($val && sessionTimeout() > 5)
		{
			if ($ctx == '')
			{
				$nPos= strpos($val, '<', $nParse);
				//	Найти открывающуюся скобку
				if (!is_int($nPos)){
					$process['tagCtx'] .= substr($val, $nParse);
					$val				= '';
					continue;
				}
				$process['tagCtx']	.= substr($val, $nParse, $nPos - $nParse);
			}else $nPos = 0;
	
			$nPosEnd = strpos($val, '>', $nPos);
			if (!is_int($nPosEnd))
			{
				$ctx	.= substr($val, $npos);
				$val	= '';
				continue;
			}
			
			$ctx	.= substr($val, $nPos, $nPosEnd - $nPos + 1);
			$ctx	= iconv('windows-1251', 'utf-8', $ctx);
			$text	= iconv('windows-1251', 'utf-8', $process['tagCtx']);
			$text	= html_entity_decode($text);
			makeImportTag(&$process, &$ctx, $text);

			$ctx	= '';
			$nParse	= $nPosEnd + 1;
			$process['offset']	= $thisOffset + $nParse;
			$process['tagCtx']	= '';
			
			if ((++$row % 50) == 0){
				//	Если запись не удалась, значит задача отменена
				if (!setImportProcess($process, false))
					return true;
			}
		}
	}
	
	$bEnd = feof($f);
	if ($bEnd) $process['offset'] = ftell($f);
	fclose($f);

	$statistic = $process['statistic'];
	importLog($process, @"Импортировано разделов: add ( <b>$statistic[categoryAdd]</b> ), update ( <b>$statistic[categoryUpdate]</b> )");
	importLog($process, @"Импортировано товаров: add ( <b>$statistic[productAdd]</b> ), update ( <b>$statistic[productUpdate]</b> )");
	
	return $bEnd;
} ?>
<? function makeImportTag(&$process, &$ctx, &$text)
{
	$bClose	= false;
	$bEndTag= false;
	$fn		= '';
	
	$nPos	= strpos($ctx, ' ');
	if (!$nPos){
		$nPos = strpos($ctx, '/>');
		if ($nPos) $bClose = true;
	}
	if (!$nPos) $nPos = strpos($ctx, '>');
	if (!$nPos) return;

	//	Close tag
	if ($ctx[1] == '/'){
		$bEndTag= true;
		$tag	= substr($ctx, 2, $nPos - 2);
		$fn		= $tag.'_close';
	}else{
		$tag	= substr($ctx, 1, $nPos - 1);
		$fn		= $tag;
		$prop	= array();
		if (preg_match_all('#(\w+)\s*=\s*[\'\"]([^\'\"]*)#u', $ctx, $vars)){
			foreach($vars[1] as $ix => $name){
				$val = $vars[2][$ix];
				$prop[$name] = html_entity_decode($val);
			}
		}
	}
	$fn = "importFn_$fn";
	if ($bClose){
		if (function_exists($fn)){
			$fn(&$process, &$tag, &$prop, &$text);
		}
		
		$fn = "importFn_$tag".'close';
		if (function_exists($fn)){
			$process['tagStack'][] = $tag;
			$fn(&$process, &$tag, &$prop, &$text);
			array_pop($process['tagStack']);
		}
	}else{
		if (function_exists($fn)){
			$fn(&$process, &$tag, &$prop, &$text);
		}
		
		if ($bEndTag){
			array_pop($process['tagStack']);
		}else{
			$process['tagStack'][] = $tag;
		}
	}
}?>
<?
//	Category tag
//	<category id="00000010413">Печать и копирование</category>
function importFn_category(&$process, &$tag, &$prop, &$text)
{
	$process['tagCategoryProp']= $prop;
}
function importFn_category_close(&$process, &$tag, &$prop, &$text)
{
	$prop		= $process['tagCategoryProp'];
	@$article	= ':'.$prop['id'];
	@$parent	= ':'.$prop['parentId'];
	@$name		= $text;
	$process['tagCategoryProp'] = NULL;
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[$article];
	@$parentId	= $cache[$parent];

	$d	= array();
	if ($id){
		if ($parentId){
			$bHasParent = $process['cacheParents']["$id:$parentId"];
			if (!$bHasParent) $d[':property'][':parent'] = $parentId;
		}
		if ($d){
			@$process['statistic']['categoryUpdate'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}
	}else{
		@$process['statistic']['categoryAdd'] += 1;
		$d['title']		= $name;
		$d[':property'][':article']	= $article;
		$d[':property'][':import']	= 'price';
		
		$id = module("doc:update:$parentId:add:catalog", $d);
		if ($id) $cache[$article] = $id;
	}
}
?>
<?
//	<offer id="00000021956" available="true">
function importFn_offer(&$process, &$tag, &$prop, &$text){
	$process['tagOfferProp'] = $prop;
}
function importFn_offer_close(&$process, &$tag, &$prop, &$text)
{
	$prop			= $process['tagOfferProp'];
	if (!$prop['id'])	 return;
	if (!$prop['name'])	return;
	
	$article		= ':'.$prop['id'];
	$parentArticle	= ':'.$prop['categoryId'];
	@$name			= $prop['name'];
	$process['tagOfferProp'] = NULL;
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];

	@$id		= $cache[$article];
	@$parentId	= $cacheParent[$parentArticle];

	$d			= array();
	@$d['price']= parseInt($prop['price']);
	@$d[':property']	= $prop[':property'];
	if (!is_array($d[':property'])) $d[':property'] = array();
	
	if ($id){
		if ($parentId){
			$bHasParent = $process['cacheParents']["$id:$parentId"];
			if (!$bHasParent) $d[':property'][':parent'] = $parentId;
		}
		if ($d){
			@$process['statistic']['productUpdate'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}
	}else{
		@$process['statistic']['productAdd'] += 1;
		$d['title']					= $name;
		$d[':property'][':article']	= $article;
		$d[':property'][':import']	= 'price';
		$id = module("doc:update:$parentId:add:product", $d);
	}
}
function tagProperty(&$process, &$tag, &$prop, &$text){
	end($process['tagStack']);
	$parentTag	= prev($process['tagStack']);
	
	switch($parentTag){
	case 'offer':
		$process['tagOfferProp'][$tag] = $text;
		break;
//	Производитель
//	<proizvoditel>
//		<Id/>
//		<Name>SAMSUNG</Name>
//	</proizvoditel>
	case 'proizvoditel':
		if ($tag != 'Name') break;
		$process['tagOfferProp'][':property']['Производитель'] = $text;
		break;
	}
}
function importFn_name_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}

function importFn_description_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_categoryId_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_price_close(&$process,&$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}
function importFn_ostatok_close(&$process, &$tag, &$prop, &$text)
{
	tagProperty(&$process, &$tag, &$prop, &$text);
}


?>
