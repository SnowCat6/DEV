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
	while(sessionTimeout() > 5){
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
	$process['importGroup']		= array();
	$process['importProduct']	= array();
	
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
		@$process['importGroup'][$id] = $prop['article']['property'];
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
		@$process['importProduct'][$id] = $prop['article']['property'];
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
		$val		= fread($f, 1024);

		while($val && sessionTimeout() > 5){
			if ($ctx == ''){
				$nPos= strpos($val, '<');
				//	Найти открывающуюся скобку
				if (!is_int($nPos)){
					$val = '';
					continue;
				}
			}else $nPos = 0;
	
			$nPosEnd = strpos($val, '>', $nPos);
			if (!is_int($nPosEnd)){
				$ctx	.= substr($val, $npos);
				$val	= '';
				continue;
			}
			$ctx .= substr($val, $nPos, $nPosEnd - $nPos + 1);
			makeImportTag(&$process, &$ctx);
	
			$ctx	= '';
			$val	= substr($val, $nPosEnd + 1);
			$process['offset']	= $thisOffset + $nPosEnd;
		}
	}
	
	$bEnd = feof($f);
	if ($bEnd) $process['offset'] = ftell($f);
	fclose($f);
	
	return $bEnd;
} ?>
<? function makeImportTag(&$process, &$ctx)
{
	$nPos	= strpos($ctx, ' ');
	if (!$nPos) $nPos = strpos($ctx, '/>');
	if (!$nPos) $nPos = strpos($ctx, '>');
	if (!$nPos) return;
	
	$tag	= substr($ctx, 1, $nPos);
	
	@$fn = $process['parseFn'];
	if ($fn) return $f($process, $ctx, $tag, $prop);
}?>

