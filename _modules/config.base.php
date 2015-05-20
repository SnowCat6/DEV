<?
//	FIRST executed config
//	Define one time used functions
$ini	= getIniValue(':');
if (!is_array($ini)){
	$ini['useCache']			= 1;
	$ini['checkCompileFiles']	= 1;
	setIniValue(':', $ini);
}

//	Добавть обработчик события
function addEvent($eventName, $eventModule)
{
	$event = getCacheValue('localEvent');

	//	Можно задавать место выполнения события
	//	addEvent('config.end:before', ...);
	list($eventName, $postfix)		= explode(':', $eventName, 2);
	if (!$postfix) $postfix = 'fire';
	//	Добавить событие
	$event[$eventName][$postfix][$eventModule]	= $eventModule;

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

//	Добавить обработку правила доступа к объектам
function addAccess($parseRule, $parseModule){
	$localAccessParse = getCacheValue('localAccessParse');
	$localAccessParse[$parseRule][$parseModule]	= $parseModule;
	setCacheValue('localAccessParse', $localAccessParse);
}

//	Добавить гнрупповую роль для администрирования
function addRole($roleName, $roleAccess){
	$localUserRoles = getCacheValue('localUserRoles');
	$localUserRoles[$roleAccess]	= $roleName;
	setCacheValue('localUserRoles', $localUserRoles);
}

//	Добавить фиксированный сниппет
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
//	Добавить папку с файлами для отслеживания
function addCompiledFolder($path)
{
	global $_COMPILED;
	foreach(scanFolder($path) as $file){
		if (is_file($file)) addCompiledFile($file);
		else addCompiledFolder($file);
	}
}
//	ПО окончании конфигурирования сохранить файлы
function module_addCompiledFile($val, $data)
{
	global $_COMPILED;
	writeData(cacheRoot . '/files.txt', $_COMPILED);
}
?>