<?
/*******************************/
//	Кеширование данных
//	Если включен Memcache, то все данные кешируются в него
//	Типы кеша:
//	mem	- Memcache кеш, работает только при включенном Memcache
//	ini	- Запись кеша вместе с системным кешем в cache.txt, используется для хранения маленьких кешей которые должны быть всегда в памяти
//	file - Кеширование в файл
//	ram	- Кеш в системной памяти, сохраняется только до момента выхода
//	docxxx	- Кеш в данных  документов, загружается при открытии документа автоматически
/*******************************/

global $_CONFIG;
//	Стек вызванных модулей во вложенных кешах
$_CONFIG['cache_data']	= array();
//	Текущая вложенность кеша
$_CONFIG['cache_level']	= 0;
/*******************************/
//	Установить значение кеша по имени, можно задать тип кеша
function setCache($label, $data, $storageID = '')
{
	if (!$label) return;

	$ev	= array(
		'id'		=> $storageID,
		'name'		=> $label,
		'content'	=> &$data
		);
	event("cache.set:$storageID", $ev);
	
	module("message:cache:set", "$storageID/$label");
}
//	Получить значение кеша по имени
function getCache($label, $storageID = '')
{
	if (!$label) return;

	$data	= '';
	$ev		= array(
		'id'		=> $storageID,
		'name' 		=> $label,
		'content'	=> &$data
		);
	event("cache.get:$storageID", $ev);
	
	if (!is_null($data)) module("message:cache:get OK", "$storageID/$label");
	else module("message:cache:get FALSE", "$storageID/$label");
	
	return $data;
}
/*******************************/
//	Очистить по ключевым словам
function clearCache($label, $storageID = '')
{
	$ev	= array(
		'id'		=> $storageID,
		'name'		=> $label
		);
	event('cache.clear', $ev);
	memClear();
}
/*******************************/
//	Добавить в стек кеша модулии необходимые для корректного отображения кешируемых объектов
//	Обычно это или стили или дополнительные скрипты вне кешируемого объекта
function setCacheData($moduleName, $moduleArguments)
{
	global $_CONFIG;
	$level	= $_CONFIG['cache_level'];
	//	Вычислить хеш, чтобы не повторять вызовы модуля
	$hash	= array($moduleName, $moduleArguments);
	$hash	= hashData($hash);
	//	Передать выполнение модуля всям нижним кешам
	while($level > 0){
		//	Прописываем выполенение модулей всем нижележащим кешам
		$_CONFIG['cache_data'][$level][$hash]	= array('name' => $moduleName, 'args' => $moduleArguments);
		--$level;
	}
}
//	Получить кешируемые модули для хранения
function getCacheData()
{
	global $_CONFIG;
	$level	= $_CONFIG['cache_level'];

	$data	= $_CONFIG['cache_data'][$level];
	if (is_array($data)) return array_values($data);
}
//	Выполнить кешируемые модули
function executeCacheData($data)
{
	if (!is_array($data)) return;
	//	Выполнить все модули с аргументами
	foreach($data as $module){
		moduleEx($module['name'], $module['args']);
	}
}
/*******************************/
//	Начать кешировать вывод
//	Если true то кеш не найден, надо создать
function beginCache($label, $storageID = '')
{
	global $_CONFIG;
	//	Получить кеш
	$data = getCache($label, $storageID);
	if ($data && is_array($data))
	{
		//	Вывести сохраненный контент, выполнить сопутствующие модули
		echo $data['content'];
		//	Выполнить дополнительныем одули, если они использовались в кеше
		executeCacheData($data['modules']);
		return false;
	}
	//	Увеличить уровень кеша, запомниить данные
	$_CONFIG['cache_level'] += 1;
	pushStackName($label, array(
		'id'		=> $storageID,
		'noCache'	=> getNoCache())
	);
	ob_start();

	return true;
}
//	Завершить запись в кеш
function endCache()
{
	//	Получить данные кеша
	$data		= getStackData();
	$storageID	= $data['id'];
	//	Если былы запрещение кеширования, отменить кеширование
	if ($data['noCache'] != getNoCache())
		return cancelCache();

	//	Сохранить контент и сопутствующие выполняемые модули
	$data		= array(
		'modules'	=> getCacheData(),
		'content'	=> ob_get_flush()
	);
	//	Записать кеш
	$key		= popStackName();
	setCache($key, $data, $storageID);

	commitCache();
}
//	Отменить кеширование
function cancelCache()
{
	ob_end_flush();
	popStackName();

	commitCache();
}
//	Удалить кеши большего вложения
function commitCache()
{
	global $_CONFIG;
	
	$level	= ($_CONFIG['cache_level'] -= 1);
	$cache	= &$_CONFIG['cache_data'];
	foreach($cache as $lvl => $val){
		if ($level < $lvl) unset($cache[$lvl]);
	}
}
/*******************************/
function memBegin($key){ return beginCache($key, 'mem'); }
function memEnd(){ return endCache();}
function memEndCancel(){ return cancelCache();}
/*******************************/
function fileBegin($key){ return beginCache($key, 'file'); }
function fileEnd(){ return endCache();}
function fileEndCancel(){ return cancelCache();}

/*******************************/
function module_cache($mode, &$ev)
{
	$name	= $ev['name'];

	switch($mode){
	case 'get':
		if (!localCacheExists()) return;
		$cache			= getCacheValue(':cache');
		$ev['content']	= $cache[$name];
		return;

	case 'set':
		$cache			= getCacheValue(':cache');
		$cache[$name]	= $ev['content'];
		setCacheValue(':cache', $cache);
		return;

	case 'clear':
		$cache	= array();
		setCacheValue(':cache', $cache);
		return;
	}
}
/*******************************/
function module_cache_file($mode, &$ev)
{
	$id		= $ev['id'];
	$name	= $ev['name'];
	$cache	= getCacheValue(':fileCache');

	if (defined('memcache'))
	{
		switch($mode){
		case 'get':
			if (!localCacheExists()) return;
			$ev['content']	= memGet("$id:$name");
			return;

		case 'set':
			if (!localCacheExists()) break;
			memSet("$id:$name", $ev['content']);
			return;
		}
	}
	$fileName	= md5($name) . '.txt';
	$dirName	= cacheRoot . '/fileCache/';
	$bUseZip	= extension_loaded("zip") && extension_loaded("phar");

	switch($mode){
	case 'get':
		if (!localCacheExists()) return;
		
		if ($bUseZip)	$dirName	= "phar://$dirName" . "cache_$fileName[0]$fileName[1].zip/";
		else $dirName	= "$dirName$fileName[0]/$fileName[1]/";

		$ev['content']	= unserialize(file_get_contents($dirName . $fileName));
		return;

	case 'set':
		if (!localCacheExists())
			return delTree($dirName, true, true);
		
		$content	= serialize($ev['content']);
		
		if ($bUseZip)
		{
			makeDir($dirName);
			$dirName= $dirName . "cache_$fileName[0]$fileName[1].zip";
			
			$zip 	= new ZipArchive;
			$zip->open($dirName, ZipArchive::CREATE);
			$zip->addFromString($fileName, $content);
			$zip->close();
		}else{
			$dirName	= "$dirName$fileName[0]/$fileName[1]/";
			makeDir($dirName);
			
			file_put_contents($dirName . $fileName, $content);
		}
		return;

	case 'clear':
		delTree($dirName, true, true);
		return;
	}
}

/*******************************/
function module_cache_ram($mode, &$ev)
{
	global $_CONFIG;
	$name	= $ev['name'];

	switch($mode){
	case 'get':
		$ev['content']	= $_CONFIG[':cache'][$name];
		return;

	case 'set':
		$_CONFIG[':cache'][$name]	= $ev['content'];
		return;

	case 'clear':
		$_CONFIG[':cache']	= array();
		return;
	}
}
?>