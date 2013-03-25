<?
function import_doImport($val, $files)
{
	if ($val){
		if (!is_array($files)) return;
		// Импортировать все файлы и массиве
		foreach($files as $path){
			getImportProcess($path, true);
		}
	}else{
		$files = getFiles(localHostPath.'/_exchange', 'xml$');
	}

	// Импортировать все файлы и массиве
	foreach($files as $path)
	{
		if (!is_file($path)) continue;
		//	Получить данные по импорту
		$process = getImportProcess($path);
		//	Есои импорт не завершен, то вывести страницу и продолжить импорт
		if (!makeImport($process))
			return setImportProcess($process, false);
		//	Если импорт завершен, заисать результат, продолжить со следующим файлом
		setImportProcess($process, true);
	}
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
			makeImportCacheGroups(&$process);
			$process['step'] = 'cacheProduct';
			break;
		//	Стадия импорта
		case 'cacheProduct':
			//	Вернуть true если требуется продолжение
			makeImportCacheProduct(&$process);
			$process['step'] = 'import';
			break;
		//	Стадия импорта
		case 'import':
			//	Вернуть true если импорт завершен
			if (makeImportImport(&$process)) return true;
			//	Вернуть false если требуется продолжение
			return false;
		}
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
		$id		= $db->id();
		$prop 	= module("prop:get:$id");
		//	Запомнить код группы и артикул (оригинальный код прайса)
		@$article	= $prop[':article'];
		if (isset($article['property'])){
			$article= "$article[property]";
			@$process['cacheGroup'][$article] = $id;
		}
		$db->clearCache();
	}
}?>
<?
//	Кешировать группы товаров
function makeImportCacheProduct(&$process)
{
	$db = module('doc');
	//	Занесение в кеш импортированых групп товаров
	$s	= array();
	$s['type']				= 'product';
	$s['prop'][':import']	= 'price';
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		$id		= $db->id();
		$prop 	= module("prop:get:$id");

		//	Запомнить код товара и артикул (оригинальный код прайса)
		@$article	= $prop[':article'];
		if (isset($article['property'])){
			$article= $article['property'];
			@$process['cacheProduct'][$article] = $id;
		}
		$db->clearCache();
	}
}?>
<?
//	Импортировать прайс
function makeImportImport(&$process)
{
	$ctx= '';
	
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
			makeImportTag(&$process, &$ctx, $text);
	
			$ctx	= '';
			$nParse	= $nPosEnd + 1;
			$process['offset']	= $thisOffset + $nParse;
			$process['tagCtx']	= '';
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
				$prop[$name] = $vars[2][$ix];
			}
		}
	}
	$fn = "importFn_$fn";
//	echo $fn, ' ';
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
		@$process['statistic']['categoryUpdate'] += 1;
		if ($parentId){
			module("prop:set:$id", array(':parent' => $parentId));
		}
	}else{
		@$process['statistic']['categoryAdd'] += 1;
		$d['title']		= $name;
		$d[':property'][':article']	= $article;
		$d[':property'][':import']	= 'price';
		if ($parentId) $d[':property'][':parent'] = $parentId;
		
		$id = module('doc:update::add:catalog', $d);
	}
//	print_r($process); die;
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
	$article		= ':'.$prop['id'];
	$parentArticle	= ':'.$prop['categoryId'];
	@$name			= $prop['name'];
	$process['tagOfferProp'] = NULL;
	
	$cacheParent= &$process['cacheGroup'];
	$cache		= &$process['cacheProduct'];

	@$id		= $cache[$article];
	@$parentId	= $cacheParent[$parentArticle];
	$d			= array();

	@$d[':property']	= $prop[':property'];
	if (!is_array($d[':property'])) $d[':property'] = array();
	
	if ($id){
		@$process['statistic']['productUpdate'] += 1;
		if ($parentId) $d[':property'][':parent']	= $parentId;
		module("prop:set:$id", $d[':property']);
	}else{
		@$process['statistic']['productAdd'] += 1;
		$d['title']					= $name;
		$d[':property'][':article']	= $article;
		$d[':property'][':import']	= 'price';
//		print_r($d); print_r($cache); die;
		$id = module('doc:update:$parentId:add:product', $d);
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
