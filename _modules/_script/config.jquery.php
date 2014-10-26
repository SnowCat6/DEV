<?
$jQuery	= array();
//	Получить имеющуюся версию jQuery
$files = getFiles(dirname(__FILE__).'/script', '^jquery-2');
if (@list($jqName, ) = each($files))
	$jQuery['jQueryVersion2']	=  $jqName;

$files = getFiles(dirname(__FILE__).'/script', '^jquery-1');
if (@list($jqName, ) = each($files))
	$jQuery['jQueryVersion']	= $jqName;

//	Получить имеющуюся версию jQueryUI
$files = getDirs(dirname(__FILE__).'/script', '^jquery-ui-');
if (@list($jqName, $jqPath) = each($files))
{
	$jQuery['jQueryUIVersion']	= $jqName;

	$jqName = 'ui-darkness';
	$jQuery['jQueryUIVersionTheme']	= $jqName;

	setCacheValue('jQuery', $jQuery);
}
addEvent('admin.tools.siteTools',	'scriptTools');
?>