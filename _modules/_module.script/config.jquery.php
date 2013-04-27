<?
//	Получить имеющуюся версию jQuery
$files = getFiles(dirname(__FILE__).'/script', '^jquery-2');
if (@list($jqName, ) = each($files))
	setCacheValue('jQueryVersion2', $jqName);

$files = getFiles(dirname(__FILE__).'/script', '^jquery-1');
if (@list($jqName, ) = each($files))
	setCacheValue('jQueryVersion', $jqName);

//	Получить имеющуюся версию jQueryUI
$files = getDirs(dirname(__FILE__).'/script', '^jquery-ui-');
if (@list($jqName, $jqPath) = each($files)){
	setCacheValue('jQueryUIVersion',$jqName);
	
	$files = getDirs("$jqPath/css");
	if(@list($jqName,) = each($files))
		setCacheValue('jQueryUIVersionTheme', $jqName);
}
?>