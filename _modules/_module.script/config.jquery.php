<?
//	Получить имеющуюся версию jQuery
if (isModernBrowser()){
	$files = getFiles(dirname(__FILE__).'/script', '^jquery-2');
}else{
	$files = getFiles(dirname(__FILE__).'/script', '^jquery-1');
}
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
function isModernBrowser()
{
	$agent		= strtolower($_SERVER['HTTP_USER_AGENT']);
	$browsers	= array("firefox", "opera", "chrome", "safari"); 
	foreach($browsers as $browser){
		if (strpos($agent, $browser)) return true;
	}
	
	return false;
}
?>