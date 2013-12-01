<? function module_site_render(&$val, &$renderedPage)
{
	global $_CONFIG;

	$_CONFIG['page']['renderLayout']	= 'body';
	$_CONFIG['noCache']					= 0;
	
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
	$pages		= getCacheValue('pages');
	if (isset($pages[$template])){
		$config['page']['layout'][$config['page']['renderLayout']] = $renderedPage;
		include($pages[$template]);
		m("message:trace", "Included $pages[$template] file");
	}else{
		echo $renderedPage;
		event('site.noTemplateFound', $config);
		module('message:url:error', "Template not found '$template'");
	}
	event('site.renderEnd', $config);
	return true;
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
?>