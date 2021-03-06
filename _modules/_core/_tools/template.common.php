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
//		m('fileLoad', 'css/core.css');
		echo "<http><body>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"ss/core.css\" />
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
	setNoCache();
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
//	+function module_date
function module_date($format, $date)
{
	if (!$date) return;
	
	if (!$format) $format = '%d.%m.%Y';
	if ($date == 'now') $date = time();

	$ru_month = array('', 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря' );
	
	$nPos	= 0;
	while(true){
		$nPos2	= strpos($format, '%', $nPos);
		if ($nPos2 === false){
			echo substr($format, $nPos);
			return;
		}
		echo substr($format, $nPos, $nPos2 - $nPos);
		$val	= $format[$nPos2+1];

		switch($val){
		case 'F':
			echo $ru_month[(int)date('n', $date)];
			break;
		default:
			echo date($val, $date);
		}
		
		$nPos = $nPos2 + 2;
	}
}
//	+function module_findTemplates
function module_findTemplates($filter, $exclude)
{
	$result	= array();
	$modules= getCacheValue('templates');
	foreach($modules as $name => $path)
	{
		if (!preg_match("#$filter#", $name)) continue;
		if ($exclude && preg_match("#$exclude#", $name)) continue;
		$result[$name]	= $path;
	};
	return $result;
}
?>