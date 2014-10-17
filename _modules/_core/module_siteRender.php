<?
//	Сгенерировать страницу по пути сайта
function module_siteRender(&$val, &$renderedPage)
{
	global $_CONFIG;
	$_CONFIG['noCache']	= 0;
	//	ссылка запроса вида /page12345.htm без протокола и параметров
	$url	= getRequestURL();

	//	Конфигурационный файл
	$ini	= getCacheValue('ini');
	//	Смотрим настройки щаблона из конфигурационного файла
	$template	= $ini[$url]['template'];
	//	Если не задано, смотрим шаблон для всех
	if (!$template) $template	= $ini[':']['template'];
	//	Если не задано, установть стандартный
	if (!$template) $template	= 'default';
	//	Собственно задать имя шаблона страницы
	setTemplate($template);
	
	//	Запуск сайта, обработка модулей вроде аудентификации пользователя
	event('site.start', $url);

	//	Full page cache
	$pageCacheName	= NULL;
	event('site.getPageCacheName', $pageCacheName);

	if ($pageCacheName)
	{
		if (defined('memcache')){
			 $renderedPage = memGet($pageCacheName);
		}else{
			$pageCacheName	= md5($pageCacheName);
			$cachePath		= cacheRoot.'/fullPageCache/';
			$renderedPage	= file_get_contents("$cachePath$pageCacheName.html");
		}
	}
	
	//	Render page
	if (!$renderedPage)
	{
		ob_start();
		//	Вывести страницу с текущем URL
		renderPage($url);
		//	Получить буффер вывода для обработки
		$renderedPage = ob_get_clean();

		//	Записать полнокешированную страницу
		if ($pageCacheName && !defined('noPageCache') && getNoCache()==0){
			if (defined('memcache')){
				memSet($pageCacheName, $renderedPage);
			}else{
				makeDir($cachePath);
				file_put_contents("$cachePath$pageCacheName.html", $renderedPage);
			}
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
	//	Событие для предобработки настроек, перезаписи ссылки, еще чего
	event('site.renderStart', $requestURL);

	//	Создать динамический контент страницы
	$renderedPage	= renderURL($requestURL);

	//	Загрузка страницы
	global $_CONFIG;
	$pageTemplate	= getTemplatePage($_CONFIG['page']['template']);
	if (is_null($pageTemplate))
	{
		$pageTemplate	= getTemplatePage('default');
		module('message:url:error', "Template not found '$template'");
	}

	//	Если шаблон страницы есть, загрузить и подставить сгенерированный контент
	if ($pageTemplate)
	{
		//	Поместить контент в хранилище по умолчанию для отображения на странице
		moduleEx('page:display', $renderedPage);
		
		ob_start();
		include($pageTemplate);
		m("message:trace", "Included $pages[$template] file");
		$renderedPage	= ob_get_clean();
	}else{
		if (is_null($pageTemplate)){
			event('site.noTemplateFound', $renderedPage);
			module('message:url:error', "Template not found '$template'");
		}
	}
	//	Возможна постобработка
	event('site.renderEnd', $renderedPage);
	//	Вывод в поток
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

	//	Увы, действительно страницы не  найдена
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
		if (!preg_match("#^/$parseRule(\.htm)$#iu", $requestURL, $parseResult)) continue;

		//	Если найден, то выполняем
		unset($parseResult[count($parseResult)-1]);
		$pageRender = mEx($parseModule, $parseResult);
		//	Если все получилось, возвращаем результат
		if ($pageRender) return $pageRender;
	}
}
//	Получить реальный габлон страницы, возможно для специфического устройства
function getTemplatePage($template)
{
	if (!$template) return '';
	
	$pages	= getCacheValue('pages');
	if (isPhone())		$pageTemplate	= $pages["phone.page.$template"];
	else if(isTablet())	$pageTemplate	= $pages["tablet.page.$template"];
	if (!$pageTemplate)	$pageTemplate	= $pages["page.$template"];
	
	return $pageTemplate;
}
//	FullpageCache and fullpage module call @moduleName:param
function module_siteRenderEnd(&$val, &$renderedPage){
	$renderedPage	= preg_replace_callback('#{@([^}]+)}#',	'siteRenderEndReplace', $renderedPage);
}
//	Fullpage module call
function siteRenderEndReplace(&$val){
	return m($val[1]);
}
?>