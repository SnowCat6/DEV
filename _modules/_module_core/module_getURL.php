<?
function module_getURL($url, &$options){
	echo getURL($url, $options);
}
//	Получить правильную ссылку из пути.
function getURL($url = '', $options = '')
{
	$v		= $url?"/$url.htm":'/';
	event('site.prepareURL', &$v);
	$options= makeQueryString($options);
	return globalRootURL.($options?"$v?$options":$v);
}
?>