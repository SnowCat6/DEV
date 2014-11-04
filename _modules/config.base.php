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

?>