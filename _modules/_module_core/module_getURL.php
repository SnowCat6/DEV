<?
function module_getURL($url, &$options){
	echo getURL($url, $options);
}
function module_getURLEx($url, &$options){
	echo getURLEx($url, $options);
}
function module_url($url, &$options){
	echo getURL($url, $options);
}
//	Получить правильную ссылку из пути.
function getURL($url = '', $options = '')
{
	if ($url == '#') $v = getRequestURL();
	else{
		$v		= $url?"/$url.htm":'/';
		event('site.prepareURL', $v);
	}
	$options= is_array($options)?makeQueryString($options):$options;
	return globalRootURL.($options?"$v?$options":$v);
}

function getURLEx($url = '', $options = ''){
	$url	= getURL($url, $options);
	$server = $_SERVER['HTTP_HOST'];;
	return "http://$server$url";
}
?>