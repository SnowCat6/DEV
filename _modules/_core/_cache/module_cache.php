<?
global $_CONFIG;
$_CONFIG['cache_data']	= array();
$_CONFIG['cache_level']	= 0;

/*******************************/
function setCache($label, $data, $storageID = '')
{
	if (!$label) return;

	module("message:cache:set", "$storageID/$label");
	memSet("$storageID:$label", $data);

	$ev	= array(
		'id'		=> $storageID,
		'name'		=> $label,
		'content'	=> &$data
		);
	event("cache.set:$storageID", $ev);
}
function getCache($label, $storageID = '')
{
	if (!$label) return;
	
	if (localCacheExists())
	{
		$data	= memGet("$storageID:$label");
		if (!is_null($data)){
			module("message:cache:get", "$storageID/$label memcache OK");
			return $data;
		}
	}

	$ev		= array(
		'id'		=> $storageID,
		'name' 		=> $label,
		'content'	=> &$data
		);
	event("cache.get:$storageID", $ev);
	
	if (!is_null($data)){
		module("message:cache:get", "$storageID/$label OK");
		memSet("$storageID:$label", $data);
	}
	
	return $data;
}
/*******************************/
//	Очистить по ключевым словам
function clearCache($label, $storageID = '')
{
	memClear("$storageID:$label");

	$ev	= array(
		'id'		=> $storageID,
		'name'		=> $label
		);
	event('cache.clear', $ev);
}
/*******************************/
//	Добавить в стек кеша модулии необходимые для корректного отображения кешируемых объектов
//	Обычно это или стили или дополнительные скрипты вне кешируемого объекта
function setCacheData($moduleName, $moduleArguments)
{
	global $_CONFIG;
	$level	= $_CONFIG['cache_level'];
	while($level > 0){
		//	Прописываем выполенение модулей всем нижележащим кешам
		$_CONFIG['cache_data'][$level][]	= array('name' => $moduleName, 'args' => $moduleArguments);
		--$level;
	}
}
//	Получить кешируемые модули для хранения
function getCacheData()
{
	global $_CONFIG;
	$d		= array();
	$data	= $_CONFIG['cache_data'];
	$level	= $_CONFIG['cache_level'];
	
	foreach($data as $lvl => &$val){
		//	Уровень кеша модулей должен быть больше, чем текущий
		if ($level > $lvl) continue;
		foreach($val as $v) $d[] = $v;
	}
	return serialize($d);
}
//	Выполнить кешируемые модули
function executeCacheData($data)
{
	$data	= unserialize($data);
	if (!is_array($data)) return;

	foreach($data as &$module){
		moduleEx($module['name'], $module['args']);
	}
}
/*******************************/
function beginCache($label, $storageID = '')
{
	$data = getCache($label, $storageID);
	if ($data && is_array($data))
	{
		//	Вывести сохраненный контент, выполнить сопутствующие модули
		echo $data['content'];
		executeCacheData($data['modules']);
		return false;
	}

	global $_CONFIG;
	$_CONFIG['cache_level'] += 1;
	
	pushStackName($label, array(
		'id'		=> $storageID,
		'noCache'	=> getNoCache())
	);
	ob_start();

	return true;
}
function endCache()
{
	$data		= getStackData();
	$storageID	= $data['id'];
	$noCache	= $data['noCache'];
	if ($noCache != getNoCache()) return cancelCache();

	//	Сохранить контент и сопутствующие выполняемые модули
	$data		= array(
		'modules'	=> getCacheData(),
		'content'	=> ob_get_flush()
	);
	$key		= popStackName();
	setCache($key, $data, $storageID);

	commitCache();
}
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
	if ($$level > 0){
		foreach($_CONFIG['cache_data'] as $lvl => &$val){
			if ($level < $lvl) $val = array();
		}
	}else{
		$_CONFIG['cache_level']	= 0;
		$_CONFIG['cache_data']	= array();
	}
}
/*******************************/
//	begin render cache
function memBegin($key)
{
	return beginCache($key, 'mem');
}
//	end render cache
function memEnd()
{
	return endCache();
}
function memEndCancel()
{
	return cancelCache();
}

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