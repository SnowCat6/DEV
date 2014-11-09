<?
addEvent('page.compile',	'cache_compile');
function module_cache_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	$thisPage	= preg_replace('#{beginCache:([^}]+)}#','<? if(beginCache("\\2")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCache}#', 			'<? endCache(); } ?>', $thisPage);
}
?>