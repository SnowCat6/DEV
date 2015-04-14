<?
//	FIRST executed config
//	Base one time used functions


//	Добавть обработчик события
function addEvent($eventName, $eventModule)
{
	$event = getCacheValue('localEvent');

	//	Можно задавать место выполнения события
	//	addEvent('config.end:before', ...);
	list($eventName, $postfix)		= explode(':', $eventName, 2);
	if (!$postfix) $postfix = 'fire';
	//	Добавить событие
	$event[$eventName][$postfix][$eventModule]= $eventModule;

	setCacheValue('localEvent', $event);
}

//	Добавить обработчки URL страницы
function addUrl($parseRule, $parseModule){
	addUrlEx("#^/$parseRule\.htm$#i", $parseModule);
}
//	Добавить обработчки URL страницы
function addUrlEx($parseRule, $parseModule)
{
	$localURLparse = getCacheValue('localURLparse');
	$localURLparse[$parseRule]	= $parseModule;
	setCacheValue('localURLparse', $localURLparse);
}

//	access
function addAccess($parseRule, $parseModule){
	$localAccessParse = getCacheValue('localAccessParse');
	$localAccessParse[$parseRule][$parseModule]	= $parseModule;
	setCacheValue('localAccessParse', $localAccessParse);
}

//	roles
function addRole($roleName, $roleAccess){
	$localUserRoles = getCacheValue('localUserRoles');
	$localUserRoles[$roleAccess]	= $roleName;
	setCacheValue('localUserRoles', $localUserRoles);
}

//	Standart snippets
function addSnippet($snippetName, $value){
	$localSnippets = getCacheValue('localSnippets');
	$localSnippets[$snippetName]	= $value;
	if (!$value) unset($localSnippets[$snippetName]);
	setCacheValue('localSnippets', $localSnippets);
}

//	Отслеживать изменения этих файлов и делать перекомпиляцию при изменении
$GLOBALS['_COMPILED'] = array();
addEvent('config.end:after', 'addCompiledFile');
function addCompiledFile($path)
{
	global $_COMPILED;
	$_COMPILED[$path]	= filemtime($path);
}
function addCompiledFolder($path)
{
	global $_COMPILED;
	foreach(scanFolder($path) as $file){
		if (is_file($file)) addCompiledFile($file);
		else addCompiledFolder($file);
	}
//	$_COMPILED[$path]	= filemtime($path);
}
function module_addCompiledFile($val, $data)
{
	global $_COMPILED;
	writeData(cacheRoot . '/files.txt', $_COMPILED);
}
?>