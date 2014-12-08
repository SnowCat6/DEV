<?
addEvent('cache.set:ini',	'cache:set');
addEvent('cache.get:ini',	'cache:get');
addEvent('cache.clear',		'cache:clear');

addEvent('cache.set:ram',	'cache_ram:set');
addEvent('cache.get:ram',	'cache_ram:get');
addEvent('cache.clear',		'cache_ram:clear');

addEvent('page.compile',	'cache_compile');
function module_cache_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	$thisPage	= preg_replace('#{beginCache:([^}]+)}#','<? if(beginCache("\\2")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCache}#', 			'<? endCache(); } ?>', $thisPage);
}
?>