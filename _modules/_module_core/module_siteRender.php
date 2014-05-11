<? function module_siteRender(&$val, &$renderedPage)
{
	global $_CONFIG;
	$_CONFIG['noCache']	= 0;
	
	$ini		= getCacheValue('ini');
	//	Смотрим настройки щаблона из конфигурационного файла
	$template	= $ini[getRequestURL()]['template'];
	//	Если не задано, смотрим шаблон для всех
	if (!$template) $template	= $ini[':']['template'];
	//	Если не задано, установть стандартный
	if (!$template) $template	= 'default';
	//	Собственно задать имя шаблона страницы
	setTemplate($template);
	
	//	Запуск сайта, обработка модулей вроде аудентификации пользователя
	event('site.start', $_CONFIG);
	
	//	Full page cache
	$pageCacheName	= NULL;
	event('site.getPageCacheName', $pageCacheName);
	if ($pageCacheName) $renderedPage = memGet($pageCacheName);
	
	//	Render page
	if (is_null($renderedPage))
	{
		ob_start();
		//	Вывести страницу с текущем URL
		renderPage(getRequestURL());
		//	Получить буффер вывода для обработки
		$renderedPage = ob_get_clean();
		if ($pageCacheName && !defined('noPageCache')){
			memSet($pageCacheName, $renderedPage);
		}
	}
	//	$renderedPage .= getmicrotime() - sessionTimeStart;
	//	Завершить все выводы на экран
	//	Возможна постобработка страницы
	event('site.end',	$renderedPage);
}

///	Обработать страницу по заданному URL и вывести в стандартный вывод
function renderPage($requestURL)
{
	$config			= &$GLOBALS['_CONFIG'];
	event('site.renderStart', $config);
	$renderedPage	= renderURL($requestURL);
	$template		= $config['page']['template'];

	//	Загрузка страницы
	$$pageTemplate	= '';
	$pages			= getCacheValue('pages');

	if (isPhone())		$pageTemplate = $pages["phone.$template"];
	else if(isTablet())	$pageTemplate = $pages["tablet.$template"];

	if (!$pageTemplate)	$pageTemplate	= $pages[$template];
	//	Если шаблон страницы есть, обработать
	if ($pageTemplate)
	{
		moduleEx('page:display', $renderedPage);
		
		ob_start();
		include($pageTemplate);
		m("message:trace", "Included $pages[$template] file");
		$renderedPage	= ob_get_clean();
	}else{
		event('site.noTemplateFound', $renderedPage);
		module('message:url:error', "Template not found '$template'");
	}
	//	Возможна постобработка
	event('site.renderEnd', $renderedPage);
	//	Вывод в потоку
	echo $renderedPage;
}

//	Вызвать обработчик URL и вернуть результат как строку
function renderURL($requestURL)
{
	$parseResult = renderURLbase($requestURL);
	//	Если все получилось, возыращаем результат
	if (isset($parseResult)) return $parseResult;

	//	Страница не найдена, но не все потеряно, возможно есть событийный обработчик
	ob_start();
	event('site.noUrlFound', $requestURL);
	$parseResult = ob_get_clean();
	//	Если все получилось, возыращаем результат
	if ($parseResult) return $parseResult;
	
	//	Увы, действительно страницы не  найдено
	ob_start();
	event('site.noPageFound', $requestURL);
	$parseResult = ob_get_clean();
	if ($parseResult) return $parseResult;
	
	module('message:url:error', "Page not found '$requestURL'");
	return NULL;
}
//	Найти обработчик URL и вернуть страницу
function renderURLbase($requestURL)
{
	global $_CACHE;
	$parseRules	= &$_CACHE['localURLparse'];
	//	Поищем обработчик URL
	foreach($parseRules as $parseRule => &$parseModule)
	{
		if (!preg_match("#^/$parseRule(\.htm$)#iu", $requestURL, $parseResult)) continue;
		//	Если найден, то выполняем
		unset($parseResult[count($parseResult)-1]);
		$pageRender = mEx($parseModule, $parseResult);
		//	Если все получилось, возвращаем результат
		if ($pageRender) return $pageRender;
	}
}
/*******************************/
function deviceDetect()
{
	define('isTablet',	false);
	define('isPhone',	false);
	return;

	@$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	//	Однозначное определение что планшет
	$pads	= 'ipad|xoom|sch-i800|playbook|tablet|kindle';	
	if (preg_match("#$pads#", $agent)){
		define('isTablet',	true);
		define('isPhone',	false);
		return;
	}
	//	Однозначное определение что телефон
	$phones	= 'iphone|ipod|blackberry|opera\smini|windows\sce|palm|smartphone|iemobile|nokia|series60|midp|mobile';	
	if (preg_match("#$phones#", $agent)){
		define('isTablet',	false);
		define('isPhone',	true);
		return;
	}
	//	Возможно планшет
	$pads	= 'android';	
	define('isTablet', preg_match("#$pads#", $agent));
	define('isPhone', false);
}
function isPhone(){
	if (defined('isPhone')) 	return isPhone;
	if (isset($_GET['phone']))	return true;

	deviceDetect();
	return isPhone;
}
function isTablet()
{
	if (defined('isTablet')) 	return isTablet;
	if (isset($_GET['tablet'])) return true;
	
	deviceDetect();
	return isTablet;
}
?>