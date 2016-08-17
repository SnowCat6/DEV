<?
addEvent('storage.get',	'file:storage:get');
addEvent('storage.set',	'file:storage:set');

addAccess('file:(.*)',	'file_file_access');
?>

<?
addEvent('page.compile:before',	'htmlImageCompile');
function module_htmlImageCompile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	
	$compiller	= new imageTagCompile('img');
	$thisPage	= $compiller->compile($thisPage);
}
?>
