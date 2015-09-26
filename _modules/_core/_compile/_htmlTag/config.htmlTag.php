<?
include_once ("_class/class.moduleCompile.php");

addEvent('page.compile:before',	'htmlTagCompile');
function module_htmlTagCompile($val, &$ev)
{
	//	Заменить HTML тег
	//	<module:имя_модуля var.name.array="value" /> или 
	//	<module:имя_модуля var_name="@">контент</<module:имя_модуля>
	//	<module:имя_модуля ?="значение" /> - вызывается только если имеется значение
	//	На вызов модуля с параметрами, значение @ заменяется на содержимое между тегами
	$thisPage	= &$ev['content'];
	
	$compiller	= new moduleTagCompile('module:|mod:');
	$thisPage	= $compiller->compile($thisPage);
}
?>