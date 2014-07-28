<?
//	+function module_redirect
function module_redirect(&$val, &$url)
{
	flushCache();
	flushGlobalCache();
	module('cookie');
	
	ob_clean();
	
	$url	= "http://$_SERVER[HTTP_HOST]$url";
	if (testValue('ajax')){
		echo "<http><body>
		<div class=\"redirectMessage\">Сейчас вы будете перенаправлены на страницу <a href=\"$url\">$url</a></div>
		<script>document.location=\"$url\"</script>
		</body></http>";
	}else{
		header("Location: $url");
	}
	die;
}
//	Отключить кеширование страниц
//	+function module_nocache
function module_nocache()
{
	if (defined('noCache')) return;
	define('noCache', true);
	
    ini_set('session.cache_limiter', 'nocache'); #добавляем HTTP заголовок Expires
    ini_set('session.cache_expire', 0);          #добавляем HTTP заголовок Cache-Control

    #header('Expires: Thu, 01 Jan 1998 00:00:00 GMT');
    #header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    #динамическая генерация даты, возможно, позволит не "отпугнуть" роботов-индексаторов поисковых систем.
    header('Expires: '       . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', strtotime('-1 day')) . ' GMT');

    # HTTP/1.1
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Cache-Control: max-age=0', false);
    # HTTP/1.0
    header('Pragma: no-cache');
}
?>