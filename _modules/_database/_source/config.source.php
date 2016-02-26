<?
addEvent('page.compile:before',	'htmlSourceCompile');
function module_htmlSourceCompile($val, &$ev)
{
	include_once ("_class/class.sourceTagCompile.php");
	$thisPage	= &$ev['content'];
	
	$compiller	= new sourceTagCompile('source');
	$thisPage	= $compiller->compile($thisPage);
	
	$compiller	= new eachTagCompile('each');
	$thisPage	= $compiller->compile($thisPage);

	$compiller	= new cacheTagCompile('cache');
	$thisPage	= $compiller->compile($thisPage);
}
?>
