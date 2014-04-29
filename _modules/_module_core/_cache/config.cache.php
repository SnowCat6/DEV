<?
addEvent('page.compile',	'cache_compile');
function module_cache_compile($val, &$thisPage)
{
	$thisPage	= preg_replace('#{beginCache:([^}]+)}#','<? if(beginCache("\\2")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCache}#', 			'<? endCache(); } ?>', $thisPage);
}
?>