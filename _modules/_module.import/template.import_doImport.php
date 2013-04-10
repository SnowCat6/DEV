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
		if ($process['status'] == 'complete') continue;

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
	$process['cacheProperty']	= array();
	
	$process['tagStack']		= array();
	$process['log']				= array();
	$process['statistic']		= array();
	$process['statistic']['categoryAdd']	= 0;
	$process['statistic']['categoryUpdate']	= 0;
	$process['statistic']['productAdd']		= 0;
	$process['statistic']['productUpdate']	= 0;
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
	//	Открыть базу
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		if (sessionTimeout() < 5) return false;
		
		//	Аолучить свойства товара
		$id		= $db->id();
		$prop 	= module("prop:get:$id");
		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':importArticle'];
		if (!isset($article['property'])) continue;
		
		//	У каталога может быть много артикулов, учтем это и запомним каждый, но правильно должен быть только один
		foreach(explode(', ', $article['property']) as $article){
			if ($article) $process['cacheGroup'][$article] = $id;
		}
		
		//	Запомним родительский каталог, если он есть
		@$parent	= $prop[':importParent'];
		foreach(explode(', ', $parent['property']) as $parent){
			if ($parent) $process['cacheParents'][$id][$parent] = true;
		}
		//	Кешируем все свойства товара, чтобы не обновлять базу не измененными свойствами
		foreach($prop as $name => $val){
			$process['cacheProperty'][$id][$name] = $val['property'];
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
		if (!isset($article['property'])) continue;
		$article= $article['property'];
		@$process['cacheProduct'][$article] = $id;

		@$parent	= $prop[':parent'];
		foreach(explode(', ', $parent['property']) as $parent){
			$process['cacheParents'][$id][$parent] = true;
		}
		//	Кешируем все свойства товара, чтобы не обновлять базу не измененными свойствами
		foreach($prop as $name => $val){
			$process['cacheProperty'][$id][$name] = $val['property'];
		}
		$db->clearCache();
	}
	return true;
}?>
<?
//	Импортировать прайс
function makeImportImport(&$process)
{
	//	Накопительный текст тега
	@$ctx	= $process['ctx'];
	//	Кол-во обработанных тегов, для сброса состояния импорта
	$row	= 0;
	//	Открвть импортируемый файл
	$f	= fopen($process['importFile'], 'r');
	//	Переместить точку чтения на предыдущую позицию
	fseek($f, $process['offset']);
	//	Считывать файл большими кусками пока он не закончится
	//	Или пока не истечет время импорта
	while(!feof($f) && sessionTimeout() > 5)
	{
		//	Запомним смещение относительно начала файла
		$thisOffset	= ftell($f);
		//	Прочитаем кусок файла
		$val		= fread($f, 1*1024*1024);
		//	Установим позицию парсинга в начало
		$nParse		= 0;
		//	Пока позволяет время, разюираем текст
		while($val && sessionTimeout() > 5)
		{
			//	Пока не найден тег, пытаемся его найти
			if ($ctx == '')
			{
				//	Ищем начало тега
				$nPos = strpos($val, '<', $nParse);
				//	Найти открывающуюся скобку
				if (!is_int($nPos)){
					//	Если не найдено начало, сохраняем межтеговый текст и считываем следующую порцию файла
					$process['tagCtx'] .= substr($val, $nParse);
					$val				= '';
					continue;
				}
				//	Начало тега найдено, запоминаем теж теговый текст
				$process['tagCtx']	.= substr($val, $nParse, $nPos - $nParse);
			}else{
				//	Начало тега найдено
				//	Такой случай может быть только, если тег считывается с начала чтения буффера
				$nPos = 0;
			}
			//	Найти конец тега
			$nPosEnd = strpos($val, '>', $nPos);
			if (!is_int($nPosEnd))
			{
				//	Еслм конца тега не найдено, сохраняем данные и считываем файл дальше
				$ctx	.= substr($val, $npos);
				$process['ctx'] = $ctx;
				$val	= '';
				continue;
			}
			//	Получить строку, содержащую весь тег
			$ctx	.= substr($val, $nPos, $nPosEnd - $nPos + 1);
			//	Перевести в UTF8
			$ctx	= iconv('windows-1251', 'utf-8', $ctx);
			//	Перевести в UTF8 межтеговый текст
			$text	= iconv('windows-1251', 'utf-8', $process['tagCtx']);
			//	Декодировать текст
			$text	= html_entity_decode($text);
			//	Обработать тег
			makeImportTag(&$process, &$ctx, $text);

			//	Сместить позицию чтения
			$nParse	= $nPosEnd + 1;
			//	Задать смещение для дальнейшего считывания
			$process['offset']	= $thisOffset + $nParse;
			//	Удалить межтеговый текст
			$process['tagCtx']	= '';
			//	Удалить содержимое тега
			$ctx	= '';
			$process['ctx']		= '';
			//	Каждые 100 строк обновлять файл импорта
			if ((++$row % 100) == 0){
				//	Если запись не удалась, значит задача отменена
				if (!setImportProcess($process, false))
					return true;
			}
		}
	}
	//	Если достигнут конец файла
	if ($bEnd = feof($f)){
		//	Задатть смещение на конец файла
		$process['offset'] = ftell($f);
	}
	//	Закрыть файл
	fclose($f);
	//	Выдать лог исполнения
	$statistic = $process['statistic'];
	importLog($process, @"Импортировано разделов: add ( <b>$statistic[categoryAdd]</b> ), update ( <b>$statistic[categoryUpdate]</b> )", 'categoryIpdate');
	importLog($process, @"Импортировано товаров: add ( <b>$statistic[productAdd]</b> ), update ( <b>$statistic[productUpdate]</b> )", 'productUpdate');
	
	return $bEnd;
} ?>
<?
//	Обработать найденый тег
function makeImportTag(&$process, &$ctx, &$text)
{
	//	true если тег закрывающий
	$bClose	= false;
	//	true если тег одиночный, и сразу закрывающий
	$bEndTag= false;
	//	Функция вызова
	$fn		= '';
	
	//	Найти пробел после названия тега
	$nPos	= strpos($ctx, ' ');
	if (!$nPos){
		//	Или найти закрывающие символы
		$nPos = strpos($ctx, '/>');
		if ($nPos) $bClose = true;
	}
	if (!$nPos) $nPos = strpos($ctx, '>');
	if (!$nPos) return;

	//	Close tag
	if ($ctx[1] == '/'){
		$bEndTag= true;
		//	Получить имя тега
		$tag	= substr($ctx, 2, $nPos - 2);
		//	Название аункции закрывающего тега
		$fn		= $tag.'_close';
	}else{
		//	Получить имя тега
		$tag	= substr($ctx, 1, $nPos - 1);
		//	Название функции открывающего тега
		$fn		= $tag;
		$prop	= array();
		//	Получить все свойства тега
		if (preg_match_all('#(\w+)\s*=\s*[\'\"]([^\'\"]*)#u', $ctx, $vars)){
			foreach($vars[1] as $ix => $name){
				$val = $vars[2][$ix];
				//	Сохранить в массиве
				$prop[$name] = html_entity_decode($val);
			}
		}
	}
	//	Назвать функцию
	$fn = "importFn_$fn";
	//	Если тег одиночный, обработать соответствюще
	if ($bClose){
		//	Вызвать открывающую функцию
		if (function_exists($fn)){
			$fn(&$process, &$tag, &$prop, &$text);
		}
		//	Удалить межтеговый текст, т.к. его не может быть в одиночном теге
		$text = '';
		//	Вызывать закрывающую функцию
		$fn = "importFn_$tag".'close';
		if (function_exists($fn)){
			//	Добавть открываюший тег в стек
			$process['tagStack'][] = $tag;
			$fn(&$process, &$tag, &$prop, &$text);
			//	Удалить открывающий тег из мтека
			array_pop($process['tagStack']);
		}
	}else{
		//	Если функция тега есть, выполнить
		if (function_exists($fn)){
			$fn(&$process, &$tag, &$prop, &$text);
		}
		
		if ($bEndTag){
			//	Если тег закрывается, удалить из стека
			array_pop($process['tagStack']);
		}else{
			//	Если тег открывающийся, добавить в стек
			$process['tagStack'][] = $tag;
		}
	}
}?>
<?
//	Category tag
//	<category id="00000010413">Печать и копирование</category>
function importFn_category(&$process, &$tag, &$prop, &$text)
{
	//	Запомнить свойства открывающего тега
	$process['tagCategoryProp']= $prop;
}
function importFn_category_close(&$process, &$tag, &$prop, &$text)
{
	//	Закрывающий тег
	$prop		= $process['tagCategoryProp'];
	@$article	= ":$prop[id]";			//	Артикул
	@$parent	= ":$prop[parentId]";	//	Родительский объект
	@$name		= $text;				//	Межтеговый текст как имя категории
	$process['tagCategoryProp'] = NULL;	//	Обнулить свойства
	
	$cache		= &$process['cacheGroup'];
	@$id		= $cache[$article];
	@$parentId	= $cache[$parent];

	$d	= array();
	if ($id){
		if ($parentId){
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent) $d[':property'][':importParent'] = $parentId;
		}
		if ($d){
			@$process['statistic']['categoryUpdate'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}
	}else{
		@$process['statistic']['categoryAdd'] += 1;
		$d['title']		= $name;
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
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
	
	$article		= ":$prop[id]";
	$parentArticle	= ":$prop[categoryId]";
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
			@$bHasParent = $process['cacheParents'][$id];
			@$bHasParent = $bHasParent[$parentId];
			if (!$bHasParent) $d[':property'][':importParent'] = $parentId;
		}
		if ($d){
			@$process['statistic']['productUpdate'] += 1;
			$id = module("doc:update:$id:edit", $d);
		}
	}else{
		@$process['statistic']['productAdd'] += 1;
		$d['title']					= $name;
		$d[':property'][':import']			= 'price';
		$d[':property'][':importArticle']	= $article;
		$d[':property'][':importParent']	= $parentId;
//		$id = module("doc:update:$parentId:add:product", $d);
	}
}
function tagProperty(&$process, &$tag, &$prop, &$text)
{
	//	Переметится на конец архива
	end($process['tagStack']);
	//	Получить родительский тег
	$parentTag	= prev($process['tagStack']);
	
	switch($parentTag){
	case 'offer':
	//	Добавть в ствойства родителя значение текущего тега
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
